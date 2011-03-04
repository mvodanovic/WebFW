<?php
namespace WebFW\Core;

final class Exception extends \Exception
{
   public function ErrorMessage()
   {
      ob_end_clean();
      echo Doctype::XHTML11;
      echo Doctype::wFW_SIG;
      include \WebFW\Config\FW_PATH . '/templates/error.template.php';
      die;
   }
}

?>
