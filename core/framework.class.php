<?php
namespace WebFW\Core;

final class Framework
{
   private static $_ctlPath = 'Application\Controllers\\';
   private static $_cmpPath = 'Application\Components\\';
   
   private static function _loadConfig()
   {
      $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'current_specifics.inc.php';

      if (!file_exists($file))
      {
         throw new Exception('Required file missing: ' . $file);
      }
      
      require_once($file);
      
      if (!defined('\Config\SPECIFICS'))
      {
         throw new Exception('Required constant \'Config\SPECIFICS\' missing in file: ' . $file);
      }

      $file = \WebFW\Config\BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'specifics' . DIRECTORY_SEPARATOR . \Config\SPECIFICS . '.inc.php';
      
      if (!file_exists($file))
      {
         throw new Exception('Required file missing: ' . $file);
      }
      
      require_once($file);
      
      if (!class_exists('\Config\Specifics\Data'))
      {
         throw new Exception('Class \'\Config\Specifics\Data\' missing in file: ' . $file);
      }
      
      if (!method_exists('\Config\Specifics\Data', 'GetItem'))
      {
         throw new Exception('Method \'GetItem\' missing in class \'\Config\Specifics\Data\' in file: ' . $file);
      }
      
      $file = \Config\Specifics\Data::GetItem('ERROR_REPORTING');
      if ($file !== null) error_reporting($file);
      
      $file = \Config\Specifics\Data::GetItem('DISPLAY_ERRORS');
      if ($file !== null) ini_set('display_errors', $file);
   }

   public static function Start()
   {
      global $wFW_Controller;

      self::_loadConfig();
      
      $name = '';
      if (array_key_exists('ctl', $_REQUEST)) $name = trim($_REQUEST['ctl']);
      if ($name === '') $name = \Config\Specifics\Data::GetItem('DEFAULT_CTL');
      if ($name === null || $name === '')
      {
         echo \WebFW\Core\Doctype::XHTML11;
         require_once \WebFW\Config\FW_PATH . '/templates/helloworld.template.php';
         return;
      }
      if (!class_exists(self::$_ctlPath . $name))
      {
         throw new Exception('Controller missing: ' . $name);
      }
      
      $name = self::$_ctlPath . $name;
      
      $wFW_Controller = new $name();
      $wFW_Controller->Init();
   }

   public static function ComponentRunner($name, &$params = null, $action = null)
   {
      global $wFW_Component;

      if (!class_exists(self::$_cmpPath . $name))
      {
         throw new Exception('Component missing: ' . $name);
      }

      $name = self::$_cmpPath . $name;

      $wFW_Component = new $name();

      if (is_string($action))
      {
         $wFW_Component->SetAction($action);
      }

      if (is_array($params))
      {
         $wFW_Component->SetParams($params);
      }

      $wFW_Component->Init();
   }

   private function __construct() {}
}

?>
