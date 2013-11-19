<?php

namespace WebFW\Cache;

use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Config;
use WebFW\Core\Exception;

abstract class Cache extends BaseClass
{
    protected static $instance = null;
    protected $keyPrefix = null;
    protected $defaultExpirationTime = null;

    /**
     * @return Cache
     * @throws \WebFW\Core\Exception If an instance cannot be created
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            $provider = Config::get('Cache', 'providerClass');
            if ($provider === null) {
                throw new Exception('Caching is not enabled.');
            }

            if (class_exists($provider) && is_subclass_of($provider, static::className())) {
                static::$instance = new $provider();
            } else {
                throw new Exception('Cache provider \'' . $provider . '\' does not exist.');
            }
        }

        return static::$instance;
    }

    public static function isEnabled()
    {
        return Config::get('Cache', 'providerClass') === null ? false : true;
    }

    abstract public function set($key, $value, $expiration = null);
    abstract public function get($key);
    abstract public function exists($key);
    abstract public function delete($key);
    abstract public function clear();
    abstract public function getStatistics();


    protected function getKey($key)
    {
        return $this->keyPrefix . $key;
    }

    protected function __construct() {}
    private function __clone() {}
}
