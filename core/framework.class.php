<?php

namespace WebFW\Core;

use Config\Specifics\Data;
use WebFW\Core\Exceptions\NotFoundException;

final class Framework
{
    private static $ctlPath = 'Application\Controllers\\';
    private static $cmpPath = 'Application\Components\\';

    private static function loadConfig()
    {
        $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'current_specifics.inc.php';

        if (!file_exists($file)) {
            throw new Exception('Required file missing: ' . $file);
        }

        require_once($file);

        if (!defined('\Config\SPECIFICS')) {
            throw new Exception('Required constant \'Config\SPECIFICS\' missing in file: ' . $file);
        }

        $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'specifics' . DIRECTORY_SEPARATOR . \Config\SPECIFICS . '.inc.php';

        if (!file_exists($file)) {
            throw new Exception('Required file missing: ' . $file);
        }

        require_once($file);

        if (!class_exists('\Config\Specifics\Data')) {
            throw new Exception('Class \'\Config\Specifics\Data\' missing in file: ' . $file);
        }

        if (!method_exists('\Config\Specifics\Data', 'GetItem')) {
            throw new Exception('Method \'GetItem\' missing in class \'\Config\Specifics\Data\' in file: ' . $file);
        }

        $file = Data::GetItem('ERROR_REPORTING');
        if ($file !== null) {
            error_reporting($file);
        }

        $file = Data::GetItem('DISPLAY_ERRORS');
        if ($file !== null) {
            ini_set('display_errors', $file);
        }
    }

    public static function Start()
    {
        static::loadConfig();

        if (Data::GetItem('ROUTER_CLASS') !== null) {
            Router::setClass(Data::GetItem('ROUTER_CLASS'));
        }

        /// Controller
        $ctl = Request::getInstance()->ctl;
        if ($ctl === null || $ctl === '') {
            $ctl = Data::GetItem('DEFAULT_CTL');
        }
        if ($ctl === null || $ctl === '') {
            require_once \WebFW\Config\FW_PATH . '/templates/helloworld.template.php';
            return;
        }

        ///Namespace
        $ns = Request::getInstance()->ns;
        if ($ns === null || $ns === '') {
            $ns = Data::GetItem('DEFAULT_CTL_NS');
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
