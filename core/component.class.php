<?php

namespace WebFW\Core;

use WebFW\Externals\PHPTemplate;

abstract class Component
{
    protected $useTemplate = true;
    protected $params = array();
    protected $templateVariables = array();
    protected $ownerObject;

    public function __construct(&$params, &$ownerObject)
    {
        $this->setDefaultParams();
        if (is_array($params)) {
            foreach ($params as $name => &$value) {
                $this->setParam($name, $value);
            }
        }

        $this->ownerObject = &$ownerObject;
    }

    public function run()
    {
        $executeResult = $this->execute();

        if ($this->useTemplate === true) {
            try {
                $template = new PHPTemplate(
                    $this->getParam('template') . '.template.php',
                    $this->getParam('templateDirectory')
                );
            } catch (Exception $e) {
                throw new Exception('Component template missing: '
                    . $this->getParam('templateDirectory')
                    . $this->getParam('template')
                    . '.template.php'
                );
            }
            $template->set('component', $this);
            $template->set('ownerObject', $this->ownerObject);
            foreach ($this->templateVariables as $name => &$value) {
                $template->set($name, $value);
            }
            return $template->fetch();
        }

        return $executeResult;
    }

    public function getOwnerObject()
    {
        return $this->ownerObject;
    }

    protected function setTplVar($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    protected function setDefaultParams()
    {
        $templateDirectory = explode('\\', get_class($this));
        $templateDirectory = strtolower(end($templateDirectory));
        $templateDirectory = \WebFW\Core\CMP_TEMPLATE_PATH
            . DIRECTORY_SEPARATOR
            . $templateDirectory
            . DIRECTORY_SEPARATOR;

        $this->setParam('templateDirectory', $templateDirectory);
        $this->setParam('template', 'default');
    }

    protected function getParam($name)
    {
        if (!array_key_exists($name, $this->params)) {
            return null;
        }

        return $this->params[$name];
    }

    protected function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    abstract protected function execute();
}
