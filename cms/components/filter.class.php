<?php

namespace WebFW\CMS\Components;

use WebFW\Core\Component;
use WebFW\Core\Exception;
use WebFW\CMS\Controller;
use WebFW\Core\Router;

class Filter extends Component
{
    public function execute()
    {
        if (!($this->ownerObject instanceof Controller)) {
            throw new Exception('Owner must be an instance of \\WebFW\\CMS\\Controller');
        }

        $filters = $this->ownerObject->getListFilters();
        $ctl = $this->ownerObject->getName();
        $ns = $this->ownerObject->getNamespace();

        if (empty($filters)) {
            $this->useTemplate = false;
            return;
        }

        $this->setTplVar('filters', $filters);
        $this->setTplVar('targetURL', Router::getInstance()->URL($ctl, 'listItems', $ns));
        $this->setTplVar('ctl', $ctl);
        $this->setTplVar('ns', $ns);
        $this->setTplVar('action', 'listItems');
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'filter');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }
}
