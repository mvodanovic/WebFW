<?php

namespace mvodanovic\WebFW\Core\Classes;

use mvodanovic\WebFW\Cache\Cache;
use mvodanovic\WebFW\Cache\Classes\tCacheable;
use mvodanovic\WebFW\Cache\Classes\CacheGroupHelper;
use mvodanovic\WebFW\Core\Exception;

class ClassHelper extends BaseClass
{
    use tCacheable;

    protected static $classExtension = '.php';

    protected static function init()
    {
        static::$isCacheEnabled = true;
        static::$cacheExpirationTime = 3600;
    }

    public static function getClasses($parentClass = null, $includeAbstractClasses = true, $directory = null)
    {
        static::init();

        if ($parentClass !== null && !class_exists($parentClass)) {
            throw new Exception('Class ' . $parentClass . ' does not exist');
        }

        if ($directory === null) {
            $directory = \mvodanovic\WebFW\Core\BASE_PATH;
        }
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new Exception('Directory ' . $directory . ' does not exist');
        }

        $list = null;
        if (static::isCacheEnabled()) {
            $list = Cache::getInstance()->get(static::getCacheKey($parentClass, $includeAbstractClasses, $directory));
        }

        if ($list === null) {
            $list = static::getClassList($parentClass, $includeAbstractClasses, $directory);
            if (static::isCacheEnabled()) {
                Cache::getInstance()->set(
                    static::getCacheKey($parentClass, $includeAbstractClasses, $directory),
                    $list,
                    static::getCacheExpirationTime()
                );
                CacheGroupHelper::append(
                    static::getCacheGroupKey(),
                    static::getCacheKey($parentClass, $includeAbstractClasses, $directory),
                    static::getCacheExpirationTime()
                );
            }
        }

        return $list;
    }

    public static function clearCache()
    {
        CacheGroupHelper::delete(static::getCacheGroupKey());
    }

    protected static function getCacheGroupKey()
    {
        return 'ClassHelper::getClasses';
    }

    protected static function getCacheKey($parentClass, $includeAbstractClasses, $directory)
    {
        return 'ClassHelper::getClasses(' . $parentClass . ',' . $includeAbstractClasses . ',' . $directory . ')';
    }

    protected static function getClassList($parentClass, $includeAbstractClasses, $directory)
    {
        $list = array();
        $handle = opendir($directory);
        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..' || $entry === '.git' || $entry === '.install') {
                continue;
            }
            $entry = $directory . DIRECTORY_SEPARATOR . $entry;
            if (defined('\mvodanovic\WebFW\Core\PUBLIC_PATH') && $entry === \mvodanovic\WebFW\Core\PUBLIC_PATH) {
                continue;
            }
            if (is_dir($entry)) {
                $list = array_merge($list, static::getClassList($parentClass, $includeAbstractClasses, $entry));
                continue;
            }
            if (substr_compare(
                    $entry,
                    static::$classExtension,
                    -strlen(static::$classExtension),
                    strlen(static::$classExtension)
                ) !== 0) {
                continue;
            }
            $entry = static::getClassNameFromFilename($entry, $includeAbstractClasses);
            if ($parentClass !== null && !is_subclass_of($entry, $parentClass)) {
                continue;
            }
            $list[] = $entry;
        }
        closedir($handle);
        return $list;
    }

    protected static function getClassNameFromFilename($filename, $includeAbstractClasses)
    {
        $fp = fopen($filename, 'r');
        $namespace = array();
        $class = '';
        $buffer = '';
        $isAbstract = false;
        $i = 0;
        if ($fp === false) {
            return null;
        }
        while ($class === '') {
            if (feof($fp)) break;

            $buffer .= fread($fp, 128);

            if (strpos($buffer, '{') === false) continue;

            $tokens = @token_get_all($buffer);

            for (; $i<count($tokens); $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace[] = $tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_ABSTRACT) {
                    $isAbstract = true;
                }

                if ($tokens[$i][0] === T_CLASS) {
                    if ($includeAbstractClasses !== true && $isAbstract === true) {
                        return null;
                    }
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i + 2][1];
                        }
                    }
                }
            }
        }

        if ($class === '') {
            return null;
        }

        return implode('\\', $namespace) . '\\' . $class;
    }
}
