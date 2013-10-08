<?php

namespace WebFW\Core;

use WebFW\Core\Exception;

class Config
{
    protected static $configData = array();

    public static function init()
    {
        static::loadConfig();
        static::parseConfigData();
    }

    protected static function loadConfig()
    {
        /// Load the main config file, throw an exception on fail
        $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'config.ini';
        if (!file_exists($file)) {
            throw new Exception('Config file missing: ' . $file);
        }
        $data = parse_ini_file($file, true, INI_SCANNER_RAW);
        if ($data === false) {
            throw new Exception('Config file ' . $file . ' has a parse error');
        }
        static::$configData = $data;

        /// Load the specifics definition file, return on fail
        $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'specifics.ini';
        if (!file_exists($file)) {
            return;
        }
        $data = parse_ini_file($file);
        if (!is_array($data) || !array_key_exists('specifics', $data)) {
            return;
        }
        $specifics = $data['specifics'];

        /// Load the specifics file, return on fail
        $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'specifics' . DIRECTORY_SEPARATOR . $specifics . '.ini';
        if (!file_exists($file)) {
            return;
        }
        $data = parse_ini_file($file, true);
        if ($data === false) {
            return;
        }
        static::$configData = array_merge(static::$configData, $data);
    }

    protected static function parseConfigData()
    {
        foreach (static::$configData as &$section) {
            foreach ($section as &$value) {
                switch (true) {
                    case strtolower($value) === 'null':
                        $value = null;
                        break;
                    case strtolower($value) === 'false':
                    case strtolower($value) === 'off':
                    case strtolower($value) === 'no':
                        $value = false;
                        break;
                    case strtolower($value) === 'true':
                    case strtolower($value) === 'on':
                    case strtolower($value) === 'yes':
                        $value = true;
                        break;
                    case substr($value, 0, 1) === "'"
                        && substr($value, strlen($value) - 1) === "'":
                    case substr($value, 0, 1) === '"'
                        && substr($value, strlen($value) - 1) === '"':
                        $value = substr($value, 1, strlen($value) - 2);
                        break;
                    case (string) (int) $value === $value:
                        $value = (int) $value;
                        break;
                    case (string) (double) $value === $value:
                        $value = (double) $value;
                        break;
                    default:
                        $value = null;
                }
            }
        }
    }

    public static function get($section, $key)
    {
        if (!array_key_exists($section, static::$configData)) {
            return null;
        }

        if (!array_key_exists($key, static::$configData[$section])) {
            return null;
        }

        return static::$configData[$section][$key];
    }
}
