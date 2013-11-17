<?php

namespace WebFW\CMS\Components;

use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Component;
use WebFW\Core\Exception;
use WebFW\CMS\ListController;
use WebFW\Core\Route;

class Filter extends Component
{
    public function execute()
    {
        /** @var $ownerObject ListController */
        $ownerObject = $this->ownerObject;

        if (!($ownerObject instanceof ListController)) {
            throw new Exception('Owner must be an instance of \\WebFW\\CMS\\ListController');
        }

        $filters = $ownerObject->getListFilters();
        $ctl = $ownerObject->className();

        if (empty($filters)) {
            $this->useTemplate = false;
            return;
        }

        $params = array();
        if ($ownerObject->isPopup()) {
            $params['popup'] = '1';
        }

        $form = new FormStart('get', new Route($ctl, null, null, $params));

        $options = array(
            'icons' => array('primary' => 'ui-icon-search'),
            'label' => 'Filter',
        );
        $submitButton = new Button(null, 'submit', $options);

        $this->setTplVar('filters', $filters);
        $this->setTplVar('form', $form);
        $this->setTplVar('submitButton', $submitButton);
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'filter');
        $this->setParam('templateDirectory', \WebFW\Core\FW_PATH . '/cms/templates/components/');
    }
}
