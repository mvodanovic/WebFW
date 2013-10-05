<?php

namespace WebFW\Core;

use Config\Specifics\Data;

class Exception extends \Exception
{
    protected $htmlOutput = true;

    public function __construct($message, $code = 500, \Exception $e = null)
    {
        parent::__construct($message, $code, $e);

        $htmlOutput = Data::GetItem('EXCEPTIONS_USE_HTML_OUTPUT');
        if (is_bool($htmlOutput)) {
            $this->htmlOutput = $htmlOutput;
        }
    }

    public function ErrorMessage()
    {
        switch ($this->code) {
            case 400:
                header('HTTP/1.1 400 Bad Request');
                $caption = '400 Bad Request';
                break;
            case 401:
                header('HTTP/1.1 401 Unauthorized');
                $caption = '401 Unauthorized';
                break;
            case 404:
                header('HTTP/1.1 404 Not Found');
                $caption = '404 Not Found';
                break;
            case 500:
            default:
                header('HTTP/1.1 500 Internal Server Error');
                $caption = '500 Internal Server Error';
                trigger_error($this->getFile() . ': ' . $this->getLine() . ': ' .  $this->getMessage());
                break;
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        include \WebFW\Config\FW_PATH . '/templates/error.template.php';
    }

    public function getDebugBacktrace($escapeStrings = true, $htmlOutput = null)
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
                    $arg = json_encode($arg, JSON_UNESCAPED_SLASHES);
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

    public function getChainedExceptions($escapeStrings = true, $htmlOutput = null)
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
