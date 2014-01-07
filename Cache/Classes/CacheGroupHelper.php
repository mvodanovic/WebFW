<?php

namespace WebFW\Framework\Cache\Classes;

use WebFW\Framework\Cache\Cache;
use WebFW\Framework\Core\Classes\BaseClass;

/**
 * Class CacheGroupHelper
 *
 * Enables working with groups of cached data.
 *
 * @package WebFW\Framework\Cache
 */
class CacheGroupHelper extends BaseClass
{
    /**
     * Prefix prepended to each cache group key.
     */
    const CACHE_GROUP_PREFIX = 'CACHE_GROUP';

    /**
     * Adds a cache key to a cache group.
     * Cache keys are stored in an array under the group name as the key.
     *
     * @param string $group Name of the cache group
     * @param string $key Key to add to the cache group
     * @param int|null $expiration How long to keep the key in the cache group
     */
    public static function append($group, $key, $expiration = null)
    {
        $groupData = Cache::getInstance()->get(static::CACHE_GROUP_PREFIX . $group);
        if (!is_array($groupData)) {
            $groupData = array();
        }
        $groupData[] = $key;
        Cache::getInstance()->set(static::CACHE_GROUP_PREFIX . $group, $groupData, $expiration);
    }

    /**
     * Deletes the caches stored under keys in the specified group.
     * Also deletes the group from cache as it's keys don't exist anymore.
     *
     * @param string $group Group whose keys' caches are to be deleted
     */
    public static function delete($group)
    {
        $groupData = Cache::getInstance()->get(static::CACHE_GROUP_PREFIX . $group);
        if (is_array($groupData)) {
            foreach ($groupData as $key) {
                Cache::getInstance()->delete($key);
            }
            Cache::getInstance()->delete(static::CACHE_GROUP_PREFIX . $group);
        }
    }
}
