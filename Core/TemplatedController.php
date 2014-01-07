<?php

namespace WebFW\Core;

use WebFW\Externals\PHPTemplate;

abstract class TemplatedController extends Controller
{
    protected $template = 'default';
    protected $useTemplate = true;
    protected $templateVariables = array();

    const DEFAULT_TEMPLATE_NAME = 'default';

    protected function __construct()
    {
        parent::__construct();

        if ($this->action !== static::DEFAULT_ACTION_NAME) {
            $this->template = strtolower($this->action);
        } else {
            $this->template = static::DEFAULT_TEMPLATE_NAME;
        }
    }

    public function processOutput()
    {
        if ($this->redirectUrl !== null) {
            $this->setRedirectUrl($this->redirectUrl, true);
        }

        if ($this->useTemplate !== true) {
            return;
        }

        $templateDir = explode('\\', static::className());
        $templateDir = strtolower(end($templateDir));
        $templateDir = \WebFW\Core\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir . DIRECTORY_SEPARATOR;

        try {
            $template = new PHPTemplate($this->template . '.template.php', $templateDir);
        } catch (Exception $e) {
            throw new Exception('Controller template missing: ' . $templateDir . $this->template . '.template.php');
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
