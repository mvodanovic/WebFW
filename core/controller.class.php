<?php

namespace WebFW\Core;

use WebFW\Cache\Classes\Cacheable;
use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Externals\PHPTemplate;
use ReflectionMethod;

abstract class Controller extends BaseClass
{
    use Cacheable;

    protected static $instance = null;

    protected $template = 'default';
    protected $useTemplate = true;
    protected $redirectUrl = null;
    protected $templateVariables = array();
    protected $action;
    protected $output;

    const DEFAULT_ACTION_NAME = 'execute';
    const DEFAULT_TEMPLATE_NAME = 'default';

    /**
     * @return Controller
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct()
    {
        if (array_key_exists('action', $_REQUEST)) {
            $this->action = trim($_REQUEST['action']);
        }
        if ($this->action === null || $this->action === '') {
            $this->action = static::DEFAULT_ACTION_NAME;
        }

        if (!method_exists($this, $this->action)) {
            $this->error404('Action not defined: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }

        $reflection = new ReflectionMethod($this, $this->action);
        if (!$reflection->isPublic()) {
            $this->error404('Action not declared as public: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }

        if ($reflection->isStatic()) {
            $this->error404('Action declared as static: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }

        if ($this->action !== static::DEFAULT_ACTION_NAME) {
            $this->template = strtolower($this->action);
        } else {
            $this->template = static::DEFAULT_TEMPLATE_NAME;
        }
    }

    public function processOutput()
    {
        if ($this->redirectUrl !== null) {
            $this->setRedirectUrl($this->redirectUrl, true);
        }

        if ($this->useTemplate !== true) {
            return;
        }

        $templateDir = explode('\\', static::className());
        $templateDir = strtolower(end($templateDir));
        $templateDir = \WebFW\Core\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir . DIRECTORY_SEPARATOR;

        try {
            $template = new PHPTemplate($this->template . '.template.php', $templateDir);
        } catch (Exception $e) {
            throw new Exception('Controller template missing: ' . $templateDir . $this->template . '.template.php');
        }
        $template->set('controller', $this);
        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        $this->output = $template->fetch();
    }

    protected function error404($debugMessage = '404 Not Found')
    {
        throw new NotFoundException($debugMessage);
    }

    final protected function setTplVar($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    protected function setRedirectUrl($url, $doRedirectNow = false)
    {
        if ($doRedirectNow === true) {
            if (array_key_exists('redirect_debug', $_REQUEST) && $_REQUEST['redirect_debug'] == 1) {
                trigger_error('Redirect: ' . $url);
            }
            header('Location: ' . $url);
            die;
        }

        $this->redirectUrl = $url;
    }

    public function executeAction()
    {
        $this->{$this->action}();
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getURL($action = null, $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        return $this->getRoute($action, $params)->getURL($escapeAmps, $rawurlencode);
    }

    public function getRoute($action = null, $params = array())
    {
        return new Route(static::className(), $action, null, $params);
    }

    public function getAction()
    {
        return $this->action;
    }
}
