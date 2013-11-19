<?php

namespace WebFW\Cache\Providers;

use WebFW\Cache\Cache;
use WebFW\Core\Config;
use WebFW\Core\Exception;

class MemcachedProvider extends Cache
{
    const DEFAULT_SERVER_PORT = 11211;
    const DEFAULT_SERVER_WEIGHT = 0;
    const MAX_KEY_LENGTH = 250;

    protected $memcachedInstance = null;

    public function __construct($servers = null, $instanceID = null, $defaultExpirationTime = null)
    {
        if ($servers === null) {
            $servers = Config::get('Cache', 'memcachedServers');
        }
        if ($servers === null) {
            throw new Exception('No Memcached servers defined');
        }
        $servers = explode(';', $servers);
        if (empty($servers)) {
            throw new Exception('No Memcached servers defined');
        }

        if ($instanceID === null) {
            $instanceID = Config::get('General', 'projectName');
        }
        $this->memcachedInstance = new \Memcached($instanceID);

        if (count($servers) !== count($this->memcachedInstance->getServerList())) {
            foreach ($servers as &$server) {
                $server = explode(':', $server);
                if (!array_key_exists(1, $server) || $server[1] == '') {
                    $server[1] = static::DEFAULT_SERVER_PORT;
                }
                if (!array_key_exists(2, $server) || $server[2] == '') {
                    $server[2] = static::DEFAULT_SERVER_WEIGHT;
                }
            }
            $this->memcachedInstance->resetServerList();
            $this->memcachedInstance->addServers($servers);
        }

        $this->keyPrefix = $instanceID;
        if ($this->keyPrefix === null) {
            $this->keyPrefix = '';
        }

        $this->defaultExpirationTime = $defaultExpirationTime;
        if ($this->defaultExpirationTime === null) {
            $this->defaultExpirationTime = Config::get('Cache', 'defaultExpirationTime');
        }
        if ($this->defaultExpirationTime === null) {
            $this->defaultExpirationTime = 0;
        }
    }

    public function set($key, $value, $expiration = null)
    {
        if ($expiration === null) {
            $expiration = $this->defaultExpirationTime;
        }
        $this->memcachedInstance->set($this->getKey($key), $value, $expiration);
    }

    public function get($key)
    {
        $value = $this->memcachedInstance->get($this->getKey($key));
        if ($value === false && $this->memcachedInstance->getResultCode() == \Memcached::RES_NOTFOUND) {
            return null;
        }

        return $value;
    }

    public function exists($key)
    {
        $value = $this->memcachedInstance->get($this->getKey($key));
        if ($value === false && $this->memcachedInstance->getResultCode() == \Memcached::RES_NOTFOUND) {
            return false;
        }

        return true;
    }

    public function delete($key)
    {
        $this->memcachedInstance->delete($this->getKey($key));
    }

    public function clear()
    {
        $this->memcachedInstance->flush();
    }

    public function getStatistics()
    {
        return $this->memcachedInstance->getStats();
    }

    protected function getKey($key)
    {
        $key = parent::getKey($key);
        if (strlen($key) > static::MAX_KEY_LENGTH) {
            $key = $this->keyPrefix . hash('sha256', $key);
        }

        return $key;
    }
}
