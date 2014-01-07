<?php

namespace WebFW\Framework\Core;

/**
 * Class Exception
 *
 * Default exception class used by WebFW.
 *
 * @package WebFW\Framework\Core
 */
class Exception extends \Exception
{
    /**
     * If set to true, log data in debug output will be stylized with HTML.
     * If set to false, plain-text log will be outputted.
     * Set through the config file: Debug > useHTMLOutput
     *
     * @internal
     * @var bool
     */
    protected $htmlOutput = true;

    /**
     * Caption used for displaying the exception.
     * It will be used in the request header, HTML title and displayed in HTML body as title.
     * Unlike the exception's $message, this is always visible to users in some way.
     * It is set automatically using the exception's $code parameter.
     *
     * @internal
     * @var string
     */
    protected $caption = null;

    /**
     * If set to true, response body will filled with data. If set to false, response body will be empty.
     *
     * @internal
     * @var bool
     */
    protected $displayResponseBody = true;

    /**
     * A list of recognized response codes and their matching captions.
     *
     * @var array
     */
    protected $messages = array(
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '500' => 'Internal Server Error',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
    );

    /**
     * Constructor.
     * 4xx and 5xx codes are treated as HTTP response codes, otherwise a 500 response code is returned.
     * Set $displayResponseBody to true if the exception is to be shown to users,
     * to false if it is to be interpreted by a machine.
     *
     * @param string|null $message If left blank, it will be the same as the $caption
     * @param int $code The error code
     * @param \Exception|null $e A chained exception, if it exists
     * @param bool $displayResponseBody Should the response body be displayed or not
     */
    public function __construct($message = null, $code = 500, \Exception $e = null, $displayResponseBody = true)
    {
        if (!array_key_exists((string) $code, $this->messages)) {
            if ($code > 400 && $code < 500) {
                $this->caption = '400 ' . $this->messages['400'];
            } else {
                $this->caption = '500 ' . $this->messages['500'];
            }
        } else {
            $this->caption = $code . ' ' . $this->messages[(string) $code];
        }

        if ($message === null) {
            $message = $this->caption;
        }

        $this->displayResponseBody = $displayResponseBody;

        parent::__construct($message, $code, $e);

        $htmlOutput = Config::get('Debug', 'useHTMLOutput');
        if (is_bool($htmlOutput)) {
            $this->htmlOutput = $htmlOutput;
        }
    }

    /**
     * If called, will stop further processing and return a response to the client.
     *
     * @internal
     */
    public function ErrorMessage()
    {
        if ($this->getCode() >= 500 && $this->getCode() <= 599) {
            trigger_error($this->getFile() . ': ' . $this->getLine() . ': ' .  $this->getMessage());
        }

        header('HTTP/1.1 ' . $this->caption);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!$this->displayResponseBody) {
            return;
        }

        include \WebFW\Framework\Core\FW_PATH . '/Core/Templates/error.template.php';
    }

    /**
     * Gets the debug backtrace of the exception.
     * If $htmlOutput is set to null, it will use the value from the project's configuration.
     *
     * @param bool $escapeStrings Should the strings be HTML escaped or not
     * @param bool|null $htmlOutput Should the list be HTML-formatted or not
     * @return array List of backtrace items
     */
    protected function getDebugBacktrace($escapeStrings = true, $htmlOutput = null)
    {
        if ($htmlOutput === null) {
            $htmlOutput = $this->htmlOutput;
        }
        if ($htmlOutput === true) {
            $backtraceFormat = '<span style="font-style: italic;">%s</span>:'
                . '<span style="font-weight: bold;">%d</span> '
                . '<span style="color: blue; font-weight: bold;">%s</span>'
                . '<span style="font-weight: bold;">%s</span>'
                . '<span style="color: red; font-weight: bold;">%s</span>'
                . '(%s)';
            $argsFormat = '<span style="color: green;">%s</span>';
        } else {
            $backtraceFormat = '%s:%d %s%s%s(%s)';
            $argsFormat = '%s';
        }

        $backtrace = array();
        foreach ($this->getTrace() as $traceItem) {
            $file = array_key_exists('file', $traceItem) ? $traceItem['file'] : '';
            $line = array_key_exists('line', $traceItem) ? $traceItem['line'] : '';
            $function = array_key_exists('function', $traceItem) ? $traceItem['function'] : '';
            $class = array_key_exists('class', $traceItem) ? $traceItem['class'] : '';
            $type = array_key_exists('type', $traceItem) ? $traceItem['type'] : '';
            $args = array();
            if (array_key_exists('args', $traceItem)) {
                foreach ($traceItem['args'] as $arg) {
                    if (defined('JSON_UNESCAPED_SLASHES')) {
                        $arg = json_encode($arg, JSON_UNESCAPED_SLASHES);
                    } else {
                        $arg = json_encode($arg);
                    }
                    $args[] = sprintf(
                        $argsFormat,
                        $escapeStrings ? htmlspecialchars($arg) : $arg
                    );
                }
            }
            $args = implode(', ', $args);

            $backtrace[] = sprintf(
                $backtraceFormat,
                $escapeStrings ? htmlspecialchars($file) : $file,
                $escapeStrings ? htmlspecialchars($line) : $line,
                $escapeStrings ? htmlspecialchars($class) : $class,
                $escapeStrings ? htmlspecialchars($type) : $type,
                $escapeStrings ? htmlspecialchars($function) : $function,
                $args
            );
        }
        return $backtrace;
    }

    /**
     * Gets the list of chained exceptions, including this one.
     * If $htmlOutput is set to null, it will use the value from the project's configuration.
     *
     * @param bool $escapeStrings Should the strings be HTML escaped or not
     * @param bool|null $htmlOutput Should the list be HTML-formatted or not
     * @return array List of backtrace items
     */
    protected function getChainedExceptions($escapeStrings = true, $htmlOutput = null)
    {
        if ($htmlOutput === null) {
            $htmlOutput = $this->htmlOutput;
        }
        if ($htmlOutput === true) {
            $chainFormat = '<span style="font-style: italic;">%s</span>:'
                . '<span style="font-weight: bold;">%d</span> '
                . '%s '
                . '<span style="color: red; font-weight: bold;">(%d)</span> '
                . '<span style="color: blue; font-weight: bold;">[%s]</span>';
        } else {
            $chainFormat = '%s:%d %s (%d) [%s]';
        }

        $e = $this;
        $exceptionList = array();
        while ($e instanceof \Exception) {
            $file = $e->getFile();
            $line = $e->getLine();
            $message = preg_replace('#\s+#mu', ' ', $e->getMessage());
            $code = $e->getCode();
            $class = get_class($e);

            $exceptionList[] = sprintf(
                $chainFormat,
                $escapeStrings ? htmlspecialchars($file) : $file,
                $escapeStrings ? htmlspecialchars($line) : $line,
                $escapeStrings ? htmlspecialchars($message) : $message,
                $escapeStrings ? htmlspecialchars($code) : $code,
                $escapeStrings ? htmlspecialchars($class) : $class
            );
            $e = $e->getPrevious();
        }

        return $exceptionList;
    }
}
