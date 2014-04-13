<?php

namespace WebFW\Framework\Core\Traits;

use WebFW\Framework\Externals\PHPTemplate;

trait tDualTemplated
{
    use tTemplated;

    protected $baseTemplate = null;
    protected $baseTemplateDirectory = null;

    /**
     * @return string|null
     * @throws \WebFW\Framework\Core\Exception
     */
    protected function processOuterTemplate()
    {
        if ($this->useTemplate !== true) {
            return null;
        }

        $template = new PHPTemplate($this->baseTemplate . '.template.php', $this->baseTemplateDirectory);

        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        return $template->fetch();
    }
}