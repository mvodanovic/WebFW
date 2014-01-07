<?php
/**
 * Framework bootstrap.
 * Loads required constants and initializes the autoloader.
 *
 * @package WebFW\Framework\Core
 */

namespace WebFW\Framework\Core;

define ('WebFW\Framework\Core\PUBLIC_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
define ('WebFW\Framework\Core\BASE_PATH', realpath(PUBLIC_PATH . '/..'));
define ('WebFW\Framework\Core\FW_PATH', realpath(BASE_PATH . '/WebFW/Framework'));
define ('WebFW\Framework\Core\GENERAL_TEMPLATE_PATH', realpath(BASE_PATH . '/Templates'));
define ('WebFW\Framework\Core\CTL_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Controllers'));
define ('WebFW\Framework\Core\BASE_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Base'));
define ('WebFW\Framework\Core\CMP_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/Components'));

spl_autoload_register(function ($class)
{
    $file = str_replace(array('\\', '_'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $class);
    $file = \WebFW\Framework\Core\BASE_PATH . DIRECTORY_SEPARATOR . $file . '.php';

    if (is_readable($file)) {
        require $file;
        return true;
    }

    return false;
});

set_include_path(get_include_path() . ':' . \WebFW\Framework\Core\BASE_PATH);
