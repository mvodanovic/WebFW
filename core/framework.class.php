<?php

namespace WebFW\Core;

use \Config\Specifics\Data;

final class Framework
{
    private static $_ctlPath = 'Application\Controllers\\';
    private static $_cmpPath = 'Application\Components\\';

    private static function _loadConfig()
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
        self::_loadConfig();

        $name = '';
        if (array_key_exists('ctl', $_REQUEST)) {
            $name = trim($_REQUEST['ctl']);
        }
        if ($name === '') {
            $name = Data::GetItem('DEFAULT_CTL');
        }
        if ($name === null || $name === '') {
            require_once \WebFW\Config\FW_PATH . '/templates/helloworld.template.php';
            return;
        }
        $namespace = static::$_ctlPath;
        if (array_key_exists('ns', $_REQUEST) && $_REQUEST['ns'] !== '') {
            $namespace = $_REQUEST['ns'];
        }
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }
        if (!class_exists($namespace . $name)) {
            self::Error404('Controller missing: ' . $name);
        }

        $name = $namespace . $name;

        $controller = new $name();
        $controller->executeAction();
        $controller->processOutput();
        echo $controller->getOutput();
    }

    public static function runComponent($name, $namespace = null, $params = null, $ownerObject = null)
    {
        if ($namespace === null) {
            $namespace = static::$_cmpPath;
        }

        $name = $namespace . $name;

        if (!class_exists($name)) {
            throw new Exception('Component missing: ' . $name);
        }

        $component = new $name($params, $ownerObject);

        return $component->run();
    }

    public static function Error404($debugMessage = '404 Not Found')
    {
        if (Data::GetItem('SHOW_DEBUG_INFO') === true) {
            throw new Exception($debugMessage, 404);
        } elseif (file_exists(Data::GetItem('ERROR_404_PAGE'))) {
            header("HTTP/1.1 404 Not Found");
            readfile(Data::GetItem('ERROR_404_PAGE'));
            die;
        } else {
            throw new Exception($debugMessage, 404);
        }
    }

    private function __construct() {}
}
