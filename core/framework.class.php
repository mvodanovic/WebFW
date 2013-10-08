<?php

namespace WebFW\Core;

use WebFW\Core\Exceptions\NotFoundException;

final class Framework
{
    private static $ctlPath = 'Application\Controllers\\';
    private static $cmpPath = 'Application\Components\\';

    public static function Start()
    {
        Config::init();

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
            require_once \WebFW\Config\FW_PATH . '/templates/helloworld.template.php';
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

        $controller = new $ctl();
        $controller->executeAction();
        $controller->processOutput();
        echo $controller->getOutput();
    }

    public static function runComponent($name, $namespace = null, $params = null, $ownerObject = null)
    {
        if ($namespace === null) {
            $namespace = static::$cmpPath;
        }

        $name = $namespace . $name;

        if (!class_exists($name)) {
            throw new Exception('Component missing: ' . $name);
        }

        $component = new $name($params, $ownerObject);

        return $component->run();
    }

    private function __construct() {}
}
