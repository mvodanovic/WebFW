<?php

namespace WebFW\Config;

define ('WebFW\Config\PUBLIC_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
define ('WebFW\Config\BASE_PATH', realpath(PUBLIC_PATH . '/..'));
define ('WebFW\Config\FW_PATH', realpath(BASE_PATH . '/webfw'));
define ('WebFW\Config\GENERAL_TEMPLATE_PATH', realpath(BASE_PATH . '/templates'));
define ('WebFW\Config\CTL_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/controllers'));
define ('WebFW\Config\BASE_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/base'));
define ('WebFW\Config\CMP_TEMPLATE_PATH', realpath(GENERAL_TEMPLATE_PATH . '/components'));
define ('WebFW\Config\APP_ID', 'WebFW');

?>
