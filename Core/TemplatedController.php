<?php

namespace WebFW\Framework\Core;

use WebFW\Framework\Externals\PHPTemplate;

abstract class TemplatedController extends Controller
{
    protected $template;
    protected $templateDirectory;
    protected $useTemplate = true;
    protected $templateVariables = array();

    const DEFAULT_TEMPLATE_NAME = 'default';

    protected function __construct()
    {
        parent::__construct();

        if ($this->action !== static::DEFAULT_ACTION_NAME) {
            $this->template = $this->action;
        } else {
            $this->template = static::DEFAULT_TEMPLATE_NAME;
        }

        $templateDir = explode('\\', static::className());
        $templateDir = end($templateDir);
        $this->templateDirectory = \WebFW\Framework\Core\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir;
    }

    public function processOutput()
    {
        if ($this->redirectUrl !== null) {
            $this->setRedirectUrl($this->redirectUrl, true);
        }

        if ($this->useTemplate !== true) {
            return;
        }

        try {
            $template = new PHPTemplate($this->template . '.template.php', $this->templateDirectory);
        } catch (Exception $e) {
            throw new Exception('Template missing in controller ' . static::className(), 500, $e);
        }
        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        $this->output = $template->fetch();
    }

    final protected function setTplVar($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
