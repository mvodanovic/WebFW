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

    public function __construct()
    {
        $this->action = static::getDefaultActionName();

        $value = '';
        if (array_key_exists('action', $_REQUEST)) {
            $value = trim($_REQUEST['action']);
        }
        if ($value !== null && $value !== '') {
            $this->action = $value;
        }

        $value = Data::GetItem('DEFAULT_CTL_TEMPLATE');
        if ($value !== null) {
            $this->template = $value;
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

        if ($this->action !== static::getDefaultActionName()) {
            $this->template = strtolower($this->action);
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

    public static function getDefaultActionName()
    {
        $action = Data::GetItem('DEFAULT_CTL_ACTION');
        if ($action === null || $action === '') {
            $action = static::DEFAULT_ACTION_NAME;
        }

        return $action;
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
        return $this->getRoute($action, $params, $escapeAmps, $rawurlencode)->getURL();
    }

    public function getRoute($action, $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        return new Route($this->ctl, $action, $this->ns, $params, $escapeAmps, $rawurlencode);
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
