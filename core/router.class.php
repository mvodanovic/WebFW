<?php
namespace WebFW\Core;

class Router
{
   private static $_instance;
   private static $_class;

   public static function GetInstance()
   {
      if (!isset(self::$_instance))
      {
         self::$_class = get_called_class();
         self::$_instance = new self::$_class;
      }

      return self::$_instance;
   }

   public static function URL($controller, $action = 'Execute', $params = array(), $escapeAmps = true, $rawurlencode = true)
   {
      if (!isset(self::$_instance))
      {
         self::$_class = get_called_class();
         self::$_instance = new self::$_class;
      }

      $amp = '&amp;';
      if ($escapeAmps !== true) $amp = '&';

      $encodeFunction = 'rawurlencode';
      if ($rawurlencode !== true) $encodeFunction = 'urlencode';

      $url = '';

      if (\Config\Specifics\Data::GetItem('APP_REWRITE_ACTIVE') === true)
      {
      }
      elseif (
      	$controller === \Config\Specifics\Data::GetItem('DEFAULT_CTL')
      	&& $action === \Config\Specifics\Data::GetItem('DEFAULT_CTL_ACTION')
      )
      {
         $url = \Config\Specifics\Data::GetItem('APP_REWRITE_BASE');
      }
      else
      {
         $url = \Config\Specifics\Data::GetItem('APP_REWRITE_BASE') . '?ctl=' . $encodeFunction($controller);
         if ($action !== 'Execute' && $action !== null) $url .= $amp . 'action=' . $encodeFunction($action);

         if (is_array($params))
         foreach ($params as $key => $value)
         {
            $key = trim($key);
            $value = trim($value);
            if ($key === '' || $value === '') continue;

            $url .= $amp . $encodeFunction($key) . '=' . $encodeFunction($value);
         }
      }

      return $url;
   }

   public static function GetClass()
   {
      return self::$_class;
   }

   final public function __clone() { throw new Exception('Router is not cloneable.'); }
   private function __construct() {}
}

?>
