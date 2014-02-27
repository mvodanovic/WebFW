<?php
/**
 * Framework bootstrap.
 * Loads required constants and initializes the autoloader.
 *
 * @package mvodanovic\WebFW
 */

namespace mvodanovic\WebFW\Core;

define ('mvodanovic\WebFW\Core\PUBLIC_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
define ('mvodanovic\WebFW\Core\BASE_PATH', realpath(PUBLIC_PATH . '/..'));
define ('mvodanovic\WebFW\Core\FW_PATH', realpath(BASE_PATH . '/mvodanovic/WebFW'));
define ('mvodanovic\WebFW\Core\GENERAL_TEMPLATE_PATH', realpath(BASE_PATH . '/Templates'));
define ('mvodanovic\WebFW\Core\CTL_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Controllers'));
define ('mvodanovic\WebFW\Core\BASE_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Base'));
define ('mvodanovic\WebFW\Core\CMP_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Components'));

spl_autoload_register(function ($class)
{
    $file = str_replace(array('\\', '_'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $class);
    $file = \mvodanovic\WebFW\Core\BASE_PATH . DIRECTORY_SEPARATOR . $file . '.php';

    if (is_readable($file)) {
        require $file;
        return true;
    }

    return false;
});

set_include_path(get_include_path() . ':' . \mvodanovic\WebFW\Core\BASE_PATH);
