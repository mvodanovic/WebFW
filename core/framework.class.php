<?php

namespace WebFW\Core;

use WebFW\Cache\Cache;
use WebFW\Core\Exceptions\NotFoundException;

/**
 * Class Framework
 *
 * Class used for executing core framework operations.
 *
 * @package WebFW\Core
 */
final class Framework
{
    /**
     * Starts the framework core.
     *
     * @throws Exception When an error occurs
     * @internal
     */
    public static function start()
    {
        Config::init();

        Request::handleIncomingRedirection();

        if (Config::get('Debug', 'errorReporting') !== null) {
            error_reporting(Config::get('Debug', 'errorReporting'));
        }

        if (Config::get('Debug', 'displayErrors') !== null) {
            ini_set('display_errors', Config::get('Debug', 'displayErrors'));
        }

        if (Config::get('General', 'routerClass') !== null) {
            Router::setClass(Config::get('General', 'routerClass'));
        }

        /** @var Controller $ctl */
        $ctl = Request::getInstance()->ctl;
        if ($ctl === null || $ctl === '') {
            $ctl = Config::get('General', 'defaultController');
        }
        if ($ctl === null || $ctl === '') {
            require_once \WebFW\Core\FW_PATH . '/core/templates/helloworld.template.php';
            return;
        }

        if (!is_subclass_of($ctl, Controller::className())) {
            throw new NotFoundException($ctl . ' is not an instance of ' . Controller::className());
        }

        if ($ctl::isCacheEnabled()) {
            $cacheKey = $ctl::className() . serialize(Request::getInstance()->getValues());
            if (Cache::getInstance()->exists($cacheKey)) {
                echo Cache::getInstance()->get($cacheKey);
                return;
            }
        }

        /** @var $controller Controller */
        $controller = new $ctl();
        if (!($controller instanceof Controller)) {
            throw new Exception('Class ' . $ctl . ' is not an instance of ' . Controller::className() . '.');
        }
        $controller->executeAction();
        $controller->processOutput();
        $controllerOutput = $controller->getOutput();

        if ($ctl::isCacheEnabled()) {
            $cacheKey = $ctl::className() . serialize(Request::getInstance()->getValues());
            Cache::getInstance()->set($cacheKey, $controllerOutput, $ctl::getCacheExpirationTime());
        }

        echo $controllerOutput;
    }

    /**
     * Runs a component and returns it's output.
     * $ownerObject can be another Component or the Controller.
     * Component with the $ownerObject set can access its public properties and methods.
     *
     * @param string $name Name of the component class
     * @param string|null $params Parameters passed to the component
     * @param Controller|Component|null $ownerObject Owner, or creator of the component
     * @return string Component's output
     * @throws Exception If the component doesn't exist or isn't an instance of Component
     */
    public static function runComponent($name, $params = null, $ownerObject = null)
    {
        /** @var Component $name */
        if (!is_subclass_of($name, Component::className())) {
            throw new Exception($name . ' is not an instance of ' . Component::className());
        }

        if ($name::isCacheEnabled()) {
            $cacheKey = $name::className() . serialize($params);
            if (Cache::getInstance()->exists($cacheKey)) {
                return Cache::getInstance()->get($cacheKey);
            }
        }

        /** @var $component Component */
        $component = new $name($params, $ownerObject);
        if (!($component instanceof Component)) {
            throw new Exception('Class ' . $name . 'is not an instance of ' . Component::className() . '.');
        }

        $componentOutput = $component->run();

        if ($name::isCacheEnabled()) {
            $cacheKey = $name::className() . serialize($params);
            Cache::getInstance()->set($cacheKey, $componentOutput, $name::getCacheExpirationTime());
        }

        return $componentOutput;
    }

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Class cannot be cloned.
     */
    private function __clone() {}
}
