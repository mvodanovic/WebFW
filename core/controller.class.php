<?php

namespace WebFW\Core;

use \WebFW\Externals\PHPTemplate;
use \WebFW\Core\Framework;
use \Config\Specifics\Data;
use \ReflectionMethod;

abstract class Controller
{
    protected $template = 'default';
    protected $useTemplate = true;
    protected $redirectUrl = null;
    protected $templateVariables = array();
    protected $action;
    protected $className;

    const DEFAULT_ACTION_NAME = 'execute';

    public function __construct()
    {
        $this->action = $this->getDefaultActionName();

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
    }

    public function init()
    {
        $action = $this->action;

        if (!method_exists($this, $action)) {
            $this->error404('Action not defined: ' . $action . ' (in controller ' . $this->className . ')');
        }

        $reflection = new ReflectionMethod($this, $action);
        if (!$reflection->isPublic()) {
            $this->error404('Action not declared as public: ' . $action . ' (in controller ' . $this->className . ')');
        }

        if ($reflection->isStatic()) {
            $this->error404('Action declared as static: ' . $action . ' (in controller ' . $this->className . ')');
        }

        if ($this->action !== $this->getDefaultActionName()) {
            $this->template = strtolower($action);
        }

        $this->$action();

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
        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        echo $template->fetch();
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

    protected function getDefaultActionName()
    {
        $action = Data::GetItem('DEFAULT_CTL_ACTION');
        if ($action === null || $action === '') {
            $action = static::DEFAULT_ACTION_NAME;
        }

        return $action;
    }
}
