<?php

namespace WebFW\Framework\CMS\Components;

use WebFW\Framework\Core\Classes\HTML\Button;
use WebFW\Framework\Core\Classes\HTML\FormStart;
use WebFW\Framework\Core\Component;
use WebFW\Framework\Core\Exception;
use WebFW\Framework\CMS\ListController;
use WebFW\Framework\Core\Route;
use WebFW\Framework\Dev\Controller;

class Filter extends Component
{
    /** @var ListController */
    protected $controller;

    public function execute()
    {
        $this->controller = Controller::getInstance();

        if (!($this->controller instanceof ListController)) {
            throw new Exception('Controller must be an instance of ' . ListController::className());
        }

        $filters = $this->controller->getListFilters();
        $ctl = $this->controller->className();

        if (empty($filters)) {
            $this->useTemplate = false;
            return;
        }

        $params = array();
        if ($this->controller->isPopup()) {
            $params['popup'] = '1';
        }

        $form = new FormStart('get', new Route($ctl, null, null, $params));

        $options = array(
            'icons' => array('primary' => 'ui-icon-search'),
            'label' => 'Filter',
        );
        $submitButton = new Button(null, Button::BUTTON_SUBMIT, $options);

        $this->setTplVar('filters', $filters);
        $this->setTplVar('form', $form);
        $this->setTplVar('submitButton', $submitButton);
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'filter');
        $this->setParam('templateDirectory', \WebFW\Framework\Core\FW_PATH . '/CMS/Templates/Components');
    }
}
