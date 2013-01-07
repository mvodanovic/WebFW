<?php

namespace WebFW\Core;

class Exception extends \Exception
{
    public function ErrorMessage()
    {
        if ($this->code === 404) {
            header("HTTP/1.1 404 Not Found");
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            trigger_error($this->getFile() . ': ' . $this->getLine() . ': ' .  $this->getMessage());
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        include \WebFW\Config\FW_PATH . '/templates/error.template.php';
        die;
    }
}
