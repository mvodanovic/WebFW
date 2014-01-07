<?php

namespace WebFW\Framework\Cache\Classes;

use WebFW\Framework\Cache\Cache;

/**
 * Trait tCacheable
 *
 * Contains logic common to all cacheable classes.
 * All cacheable classes should use this trait.
 *
 * @package WebFW\Framework\Cache
 */
trait tCacheable
{
    /**
     * @var bool Flag indicating wheather caching is enabled for the implementing class
     */
    protected static $isCacheEnabled = false;

    /**
     * @var int|null How long to store cache; 0 for infinite, null for project default
     */
    protected static $cacheExpirationTime = null;

    /**
     * Checks if caching is enabled for implementing class.
     * Will return false if caching is disabled on project level.
     *
     * @return bool True if caching is enabled for this class, false otherwise
     * @see Cache::isEnabled()
     */
    public static function isCacheEnabled()
    {
        return static::$isCacheEnabled && Cache::isEnabled();
    }

    /**
     * Returns the expiration time set in the implementing class.
     *
     * @return int|null The expiration time
     */
    public static function getCacheExpirationTime()
    {
        return static::$cacheExpirationTime;
    }
}
