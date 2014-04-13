<?php

namespace WebFW\Framework\Core;

use WebFW\Framework\Core\Traits\tTemplated;

abstract class TemplatedController extends Controller
{
    use tTemplated;

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

        try {
            $this->output = $this->processTemplate();
        } catch (Exception $e) {
            throw new Exception('Template missing in controller ' . static::className(), 500, $e);
        }
    }

    public function getOutput()
    {
        return $this->output;
    }
}
