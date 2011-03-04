<?php
namespace WebFW\Core;

abstract class Component
{
   protected $template = 'default';
   protected $useTemplate = true;
   protected $param = array();
   private $_action = 'Execute';
   private $_className;

   final public function __construct()
   {
      $this->_className = get_class($this);
   }

   final public function Init()
   {
      $action = $this->_action;

      if (!method_exists($this, $action))
      {
         throw new Exception('Action not defined: ' . $action . ' (in component ' . $this->_className . ')');
      }

      $this->setDefaultParams();

      $this->beforeWork();

      $this->$action();

      if ($this->useTemplate === true)
      {
         $template = explode('\\', $this->_className);
         $template = \WebFW\Config\CMP_TEMPLATE_PATH . DIRECTORY_SEPARATOR . strtolower(end($template)) . DIRECTORY_SEPARATOR . strtolower($this->template) . '.template.php';
         if (!file_exists($template))
         {
            throw new Exception('Component template missing: ' . $template);
         }

         include $template;
      }

      $this->afterWork();
   }

   final public function SetAction($action)
   {
      if (is_string($action))
      $this->_action = $action;
   }

   final public function SetParams(&$params)
   {
      if (is_array($params))
      foreach ($params as $key => &$value)
      {
         $this->param[$key] = $value;
      }
   }

   protected function setDefaultParams()
   {
   }

   protected function beforeWork()
   {
   }

   protected function afterWork()
   {
   }
}

?>
