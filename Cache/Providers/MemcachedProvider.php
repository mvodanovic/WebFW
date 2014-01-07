<?php

namespace WebFW\Cache\Providers;

use WebFW\Cache\Cache;
use WebFW\Core\Config;
use WebFW\Core\Exception;

/**
 * Class MemcachedProvider
 *
 * Implements the caching mechanism using Memcached as a service.
 *
 * @package WebFW\Cache
 */
class MemcachedProvider extends Cache
{
    /**
     * Default port number to use for Memcached servers if it's not explicitly defined in server definition.
     */
    const DEFAULT_SERVER_PORT = 11211;

    /**
     * Default weight for Memcached servers if it's not explicitly defined in server definition.
     */
    const DEFAULT_SERVER_WEIGHT = 0;

    /**
     * Maximum allowed key length. Longer keys are hashed to reduce their size.
     */
    const MAX_KEY_LENGTH = 250;

    protected $memcachedInstance = null;

    /**
     * Class constructor.
     * Multiple servers can be separated by a semicolon.
     * Each server is defined by an IP address, port and weight, separated by colons.
     * Server port and server weight are optional. If not specified, default values are used.
     * If no servers are given, they will be fetched from the config file.
     * If the instance ID is not given, the project name will be used as instance ID.
     * If the expiration time is not given, expiration time from the config file will be used.
     *
     * @param string|null $servers List of Memcached servers to use
     * @param string|null $instanceID Instance ID to use
     * @param string|null $defaultExpirationTime Default expiration time of cache
     * @throws Exception If there are no servers defined in the constructor parameter or config file.
     */
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
    protected function store($key, $value, $expiration = null)
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

    /**
     * Returns a hashed version of the key if the desired key is too long.
     *
     * @param string $key The desired key
     * @return string The same key if it is short enough, otherwise a hashed version of it
     */
    protected function getKey($key)
    {
        $key = parent::getKey($key);
        if (strlen($key) > static::MAX_KEY_LENGTH) {
            $key = $this->keyPrefix . hash('sha256', $key);
        }

        return $key;
    }
}
