<?php
/**
 * Framework bootstrap.
 * Loads required constants and initializes the autoloader.
 *
 * @package WebFW\Core
 */

namespace WebFW\Core;

define ('WebFW\Core\PUBLIC_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
define ('WebFW\Core\BASE_PATH', realpath(PUBLIC_PATH . '/..'));
define ('WebFW\Core\FW_PATH', realpath(BASE_PATH . '/webfw'));
define ('WebFW\Core\GENERAL_TEMPLATE_PATH', realpath(BASE_PATH . '/templates'));
define ('WebFW\Core\CTL_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/controllers'));
define ('WebFW\Core\BASE_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/base'));
define ('WebFW\Core\CMP_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/components'));

spl_autoload_extensions('.class.php,.interface.php');
spl_autoload_register(function ($class)
{
    spl_autoload(str_replace('\\', '/', $class));
});

set_include_path(get_include_path() . ':' . \WebFW\Core\BASE_PATH);
