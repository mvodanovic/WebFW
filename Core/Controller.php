<?php

namespace mvodanovic\WebFW\Core;

use mvodanovic\WebFW\Cache\Classes\tCacheable;
use mvodanovic\WebFW\Core\Classes\BaseClass;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use ReflectionMethod;

abstract class Controller extends BaseClass
{
    use tCacheable;

    protected static $instance = null;

    protected $redirectUrl = null;
    protected $action;
    protected $output;

    const DEFAULT_ACTION_NAME = 'execute';

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
        $this->action = Request::getInstance()->action;
        if ($this->action === null || $this->action === '') {
            $this->action = static::DEFAULT_ACTION_NAME;
        }

        if (!method_exists($this, $this->action)) {
            throw new NotFoundException('Action not defined: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }

        $reflection = new ReflectionMethod($this, $this->action);
        if (!$reflection->isPublic()) {
            throw new NotFoundException('Action not declared as public: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }

        if ($reflection->isStatic()) {
            throw new NotFoundException('Action declared as static: ' . $this->action
                . ' (in controller ' . static::className() . ')');
        }
    }

    public function processOutput()
    {
        if ($this->redirectUrl !== null) {
            $this->setRedirectUrl($this->redirectUrl, true);
        }
    }

    protected function setRedirectUrl($url, $doRedirectNow = false)
    {
        if ($doRedirectNow === true) {
            if (Request::getInstance()->redirect_debug == 1) {
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
