<?php
namespace WebFW\Core;

abstract class Controller
{
   protected $template = 'default';
   protected $baseTemplate = 'default';
   protected $pageTitle = '';
   protected $useTemplate = true;
   protected $simpleOutput = false;
   protected $doctype = 'XHTML11';

   private $_action = 'Execute';
   private $_className;
   private $_urlJS = array();
   private $_urlCSS = array();
   private $_htmlMeta = array();
   private $_customHtmlHead = '';

   final public function __construct()
   {
      $value = '';
      if (array_key_exists('action', $_REQUEST)) $value = trim($_REQUEST['action']);
      if ($value === '') $value = \Config\Specifics\Data::GetItem('DEFAULT_CTL_ACTION');
      if ($value !== null && $value !== '') $this->_action = $value;

      $value = \Config\Specifics\Data::GetItem('DEFAULT_CTL_TEMPLATE');
      if ($value !== null) $this->template = $value;

      $value = \Config\Specifics\Data::GetItem('DEFAULT_BASE_TEMPLATE');
      if ($value !== null) $this->baseTemplate = $value;

      $value = \Config\Specifics\Data::GetItem('DEFAULT_DOCTYPE');
      if ($value === null) $value = $this->doctype;
      $value = '\WebFW\Core\Doctype::' . $value;
      if (defined($value))  $this->doctype = constant($value);

      $this->_className = get_class($this);
   }

   final public function GetTitle()
   {
      return $this->pageTitle;
   }

   final public function Init()
   {
      global $wFW_HtmlBody, $wFW_HtmlHead, $wFW_PageTitle, $wFW_Controller;

      ob_start();

      $action = $this->_action;

      if (!method_exists($this, $action))
      {
         $this->error404('Action not defined: ' . $action . ' (in controller ' . $this->_className . ')');
      }

      $this->$action();

      if ($this->useTemplate === true)
      {
         $template = explode('\\', $this->_className);
         $template = \WebFW\Config\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . strtolower(end($template)) . DIRECTORY_SEPARATOR . strtolower($this->template) . '.template.php';
         if (!file_exists($template))
         {
            throw new Exception('Controller template missing: ' . $template);
         }

         include $template;
      }

      foreach ($this->_urlJS as &$url)
      {
         $wFW_HtmlHead .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
      }

      foreach ($this->_urlCSS as &$data)
      {
         switch ($data['xhtml'])
         {
            case true:  $wFW_HtmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '" />' . "\n"; break;
            default:    $wFW_HtmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '">' . "\n"; break;
         }
      }

      foreach ($this->_htmlMeta as &$data) // key, content, keyType=name, scheme='', xhtml=true
      {
         if ($data['keyType'] !== 'name' && $data['keyType'] !== 'http-equiv') continue;

         if ($data['scheme'] !== '') $data['scheme'] = ' scheme="' . $data['scheme'] . '"';

         switch ($data['xhtml'])
         {
            case true:  $wFW_HtmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . ' />' . "\n"; break;
            default:    $wFW_HtmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . '>' . "\n"; break;
         }
      }

      $wFW_HtmlHead .= $this->_customHtmlHead;

      $wFW_HtmlBody = ob_get_contents();
      $wFW_PageTitle = $this->pageTitle;
      ob_end_clean();

      ob_start();
      if ($this->simpleOutput === false)
      {
         $baseTemplate = \WebFW\Config\BASE_TEMPLATE_PATH . DIRECTORY_SEPARATOR . strtolower($this->baseTemplate) . '.template.php';
         if (!file_exists($baseTemplate))
         {
            throw new Exception('Base template missing: ' . $baseTemplate);
         }

         echo $this->doctype;
         echo \WebFW\Core\Doctype::wFW_SIG;
         include $baseTemplate;
      }
      else
      {
         echo $wFW_HtmlBody;
      }
      ob_end_flush();
   }

   final protected function setLinkedJavaScript($url)
   {
      $this->_urlJS[] = $url;
   }

   final protected function setLinkedCSS($url, $xhtml = true)
   {
      $this->_urlCSS[] = array(
         'url'    => $url,
         'xhtml'  => $xhtml === true ? true : false
      );
   }

   final protected function setHtmlMeta($key, $content, $keyType = 'name', $scheme = '', $xhtml = true)
   {
      $this->_htmlMeta[] = array(
         'key'       => $key,
         'content'   => $content,
         'keyType'   => strtolower($keyType) === 'name' ? 'name' : 'http-equiv',
         'scheme'    => $scheme,
         'xhtml'     => $xhtml === true ? true : false
      );
   }

   final protected function setCustomHtmlHead($html)
   {
      $this->_customHtmlHead = $html;
   }

   protected function error404($debugMessage = '404 Not Found')
   {
      \WebFW\Core\Framework::Error404($debugMessage);
   }
}

?>