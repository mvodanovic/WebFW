<?php

namespace WebFW\Cache;

use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Config;
use WebFW\Core\Exception;
use WebFW\Dev\Classes\DevHelper;

/**
 * Class Cache
 *
 * Basic interface for the caching mechanism.
 * All cache provider interfaces should extend this class.
 *
 * @package WebFW\Cache
 */
abstract class Cache extends BaseClass
{
    /**
     * Instance of the used cache provider.
     * @var Cache
     */
    protected static $instance = null;

    /**
     * Prefix appended to all cache keys.
     * @var string
     */
    protected $keyPrefix = null;

    /**
     * Default expiration time to use for the cache if not explicitly given.
     * @var int|null
     */
    protected $defaultExpirationTime = null;

    /**
     * Get the instance of the used cache provider.
     *
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

    /**
     * Checks if caching is enabled on project level.
     *
     * @return bool If caching is enabled, false otherwise
     */
    public static function isEnabled()
    {
        return Config::get('Cache', 'providerClass') === null ? false : true;
    }

    /**
     * Set a new value in cache under the given key for the given time.
     * If the expiration time is 0, the value will be stored until explicitly removed.
     * If the expiration time is NULL, default system expiration time will be used.
     * Storing in cache is disabled on dev requests. In that case this method will have no effect.
     *
     * An external wrapper for the internal store() method which implements cache storing logic.
     *
     * @param string $key The key under which to store the value
     * @param mixed $value The value to store
     * @param int|null $expiration Time to keep the value stored in cache
     * @see store()
     */
    final public function set($key, $value, $expiration = null)
    {
        if (!DevHelper::isDevRequest()) {
            $this->store($key, $value, $expiration);
        }
    }

    /**
     * Store a new value in cache under the given key for the given time.
     *
     * @param string $key The key under which to store the value
     * @param mixed $value The value to store
     * @param int|null $expiration Time to keep the value stored in cache
     * @see set()
     */
    abstract protected function store($key, $value, $expiration = null);

    /**
     * Get the value from cache with the given key.
     *
     * @param string $key The key for which to fetch the value
     * @return mixed The cache value, NULL if it doesn't exist.
     */
    abstract public function get($key);

    /**
     * Check if a value with the given key exists in cache.
     * @param string $key The key to check cache for
     * @return bool True if the value exists, false otherwise
     */
    abstract public function exists($key);

    /**
     * Delete a value from cache with the given key.
     *
     * @param string $key The key to delete the value for
     */
    abstract public function delete($key);

    /**
     * Completely clear all cached values.
     */
    abstract public function clear();

    /**
     * Get a list of key-value pairs with cache usage statistics.
     * If not supported by the used cache provider, NULL is returned.
     *
     * @return array|null A list of key-value pairs
     */
    abstract public function getStatistics();

    /**
     * Get the key prepared to use with the cache provider from the desired key.
     *
     * @param string $key The desired key
     * @return string The prepared key
     */
    protected function getKey($key)
    {
        return $this->keyPrefix . $key;
    }

    /**
     * This class is a singleton.
     */
    protected function __construct() {}

    /**
     * This class cannot be cloned.
     */
    private function __clone() {}
}
