<?php

namespace WebFW\Core;

use WebFW\Externals\PHPTemplate;
use WebFW\Core\Framework;
use Config\Specifics\Data;
use ReflectionMethod;

abstract class Controller
{
    protected $template = 'default';
    protected $useTemplate = true;
    protected $redirectUrl = null;
    protected $templateVariables = array();
    protected $action;
    protected $className;
    protected $ctl;
    protected $ns;
    protected $output;

    const DEFAULT_ACTION_NAME = 'execute';
    const DEFAULT_TEMPLATE_NAME = 'default';

    public function __construct()
    {
        if (array_key_exists('action', $_REQUEST)) {
            $this->action = trim($_REQUEST['action']);
        }
        if ($this->action === null || $this->action === '') {
            $this->action = static::DEFAULT_ACTION_NAME;
        }

        $this->className = get_class($this);
        $separator = strrpos($this->className, '\\') + 1;
        $this->ns = '\\' . substr($this->className, 0, $separator);
        $this->ctl = substr($this->className, $separator);

        if (!method_exists($this, $this->action)) {
            $this->error404('Action not defined: ' . $this->action . ' (in controller ' . $this->className . ')');
        }

        $reflection = new ReflectionMethod($this, $this->action);
        if (!$reflection->isPublic()) {
            $this->error404('Action not declared as public: ' . $this->action . ' (in controller ' . $this->className . ')');
        }

        if ($reflection->isStatic()) {
            $this->error404('Action declared as static: ' . $this->action . ' (in controller ' . $this->className . ')');
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

        $templateDir = explode('\\', $this->className);
        $templateDir = strtolower(end($templateDir));
        $templateDir = \WebFW\Config\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir . DIRECTORY_SEPARATOR;

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
        Framework::Error404($debugMessage);
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

    public function getURL($action, $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        return $this->getRoute($action, $params)->getURL($escapeAmps, $rawurlencode);
    }

    public function getRoute($action, $params = array())
    {
        return new Route($this->ctl, $action, $this->ns, $params);
    }

    public function getName()
    {
        return $this->ctl;
    }

    public function getNamespace()
    {
        return $this->ns;
    }

    public function getAction()
    {
        return $this->action;
    }
}
