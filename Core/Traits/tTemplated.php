<?php

namespace WebFW\Framework\Core\Traits;

use WebFW\Framework\Externals\PHPTemplate;

trait tTemplated
{
    protected $template = null;
    protected $templateDirectory = null;
    protected $useTemplate = true;
    protected $templateVariables = array();

    /**
     * @return string|null
     * @throws \WebFW\Framework\Core\Exception
     */
    protected function processTemplate()
    {
        if ($this->useTemplate !== true) {
            return null;
        }

        $template = new PHPTemplate($this->template . '.template.php', $this->templateDirectory);

        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        return $template->fetch();
    }

    final protected function setTplVar($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }
}