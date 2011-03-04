<?php

namespace WebFW\Config;

require_once('const.inc.php');

function Bootstrap()
{
   spl_autoload_extensions('.class.php');
   spl_autoload_register(function ($class)
   {
      return spl_autoload(str_replace('\\', '/', $class));
   });
   
   set_include_path(get_include_path() . ':' . \WebFW\Config\BASE_PATH);
}

?>
