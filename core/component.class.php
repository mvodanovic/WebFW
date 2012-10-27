<?php
namespace WebFW\Core;

abstract class Component
{
   protected $template = 'default';
   protected $useTemplate = true;
   protected $param = array();
   protected $_action = 'Execute';
   protected $_className;
   protected $templateVariables = array();

   public function __construct()
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
         $templateDir = explode('\\', $this->_className);
         $templateDir = strtolower(end($templateDir));
         $templateDir = \WebFW\Config\CMP_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir . DIRECTORY_SEPARATOR;

         try {
            $template = new \WebFW\Externals\PHPTemplate($this->template . '.template.php', $templateDir);
         } catch (Exception $e) {
            throw new Exception('Component template missing: ' . $templateDir . $this->template . '.template.php');
         }
         foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
         }
         return $template->fetch();
      }

      $this->afterWork();
   }

   final protected function SetTplVar($name, $value)
   {
      $this->templateVariables[$name] = $value;
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
