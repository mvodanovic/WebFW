<?php

namespace WebFW\Core;

use WebFW\CMS\Controller;
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
     * Default controller path, fallback if path cannot be obtained otherwise.
     *
     * @var string
     * @internal
     */
    private static $ctlPath = 'Application\Controllers\\';

    /**
     * Default component path, fallback if path cannot be obtained otherwise.
     *
     * @var string
     * @internal
     */
    private static $cmpPath = 'Application\Components\\';

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

        /// Controller
        $ctl = Request::getInstance()->ctl;
        if ($ctl === null || $ctl === '') {
            $ctl = Config::get('General', 'defaultController');
        }
        if ($ctl === null || $ctl === '') {
            require_once \WebFW\Core\FW_PATH . '/core/templates/helloworld.template.php';
            return;
        }

        ///Namespace
        $ns = Request::getInstance()->ns;
        if ($ns === null || $ns === '') {
            $ns = Config::get('General', 'defaultControllerNamespace');
        }
        if ($ns === null || $ns === '') {
            $ns = static::$ctlPath;
        }
        if (substr($ns, -1) !== '\\') {
            $ns .= '\\';
        }

        $ctl = $ns . $ctl;
        if (!class_exists($ctl)) {
            throw new NotFoundException('Controller missing: ' . $ctl);
        }

        /** @var $controller Controller */
        $controller = new $ctl();
        if (!($controller instanceof Controller)) {
            throw new Exception('Class ' . $ctl . 'is not an instance of ' . Controller::className() . '.');
        }
        $controller->executeAction();
        $controller->processOutput();
        echo $controller->getOutput();
    }

    /**
     * Runs a component and returns it's output.
     * $ownerObject can be another Component or the Controller.
     * Component with the $ownerObject set can access its public properties and methods.
     *
     * @param string $name Name of the component class
     * @param string|null $namespace Namespace of the component
     * @param string|null $params Parameters passed to the component
     * @param Controller|Component|null $ownerObject Owner, or creator of the component
     * @return string Component's output
     * @throws Exception If the component doesn't exist or isn't an instance of Component
     */
    public static function runComponent($name, $namespace = null, $params = null, $ownerObject = null)
    {
        if ($namespace === null) {
            $namespace = static::$cmpPath;
        }

        $name = $namespace . $name;

        if (!class_exists($name)) {
            throw new Exception('Component missing: ' . $name);
        }

        /** @var $component Component */
        $component = new $name($params, $ownerObject);
        if (!($component instanceof Component)) {
            throw new Exception('Class ' . $name . 'is not an instance of ' . Component::className() . '.');
        }

        return $component->run();
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
