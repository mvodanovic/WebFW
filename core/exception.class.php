<?php
namespace WebFW\Core;

final class Exception extends \Exception
{
   public function ErrorMessage()
   {
      if ($this->code === 404)
      {
         header("HTTP/1.1 404 Not Found");
      }
      else
      {
         header("HTTP/1.1 500 Internal Server Error");
      }
      ob_end_clean();
      echo Doctype::XHTML11;
      echo Doctype::wFW_SIG;
      include \WebFW\Config\FW_PATH . '/templates/error.template.php';
      die;
   }
}

?>
