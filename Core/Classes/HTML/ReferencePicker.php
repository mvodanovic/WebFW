<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\CompoundFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Base\GeneralHTMLItem;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Core\Route;
use mvodanovic\WebFW\Database\TableColumns\Column;
use mvodanovic\WebFW\Database\TableConstraints\ForeignKey;
use mvodanovic\WebFW\Database\TableGateway;

class ReferencePicker extends CompoundFormItem
{
    protected $route;
    protected $foreignKey;
    protected $tableGatewayName;
    protected $caption = null;

    public function __construct(Route $route, ForeignKey $foreignKey, $tableGatewayName)
    {
        if (!is_subclass_of($tableGatewayName, TableGateway::className())) {
            throw new Exception('Invalid table gateway set');
        }

        $this->route = $route;
        $this->foreignKey = $foreignKey;
        $this->tableGatewayName = $tableGatewayName;
        foreach ($foreignKey->getColumns() as $column) {
            /** @var Column $column */
            $this->values[$column->getName()] = null;
        }

        parent::__construct();
    }

    protected function getCaption()
    {
        if ($this->caption === null) {
            /** @var TableGateway $tableGateway */
            $tableGateway = new $this->tableGatewayName();
            try {
                $tableGateway->loadBy($this->values);
                $this->caption = $tableGateway->getCaption();
            } catch (NotFoundException $e) {}
        }

        return $this->caption;
    }

    public function parse()
    {
        $formElement = new GeneralHTMLItem('span');
        $formElement->addClass('caption');
        $formElement->setStyle('display', 'inline-block');
        $formElement->setStyle('margin-right', '0.3em');
        $formElement->setInnerHTML(htmlspecialchars($this->getCaption()));
        $innerHTML = $formElement->parse();

        $formElement = new Button(
            null,
            Button::BUTTON_BUTTON,
            array('icons' => array('primary' => 'ui-icon-pencil'), 'text' => false)
        );
        $formElement->addClass('select');
        $formElement->setStyle('margin-right', '0.3em');
        $innerHTML .= $formElement->parse();

        $formElement = new Button(
            null,
            Button::BUTTON_BUTTON,
            array('icons' => array('primary' => 'ui-icon-close'), 'text' => false)
        );
        $formElement->addClass('clear');
        $innerHTML .= $formElement->parse();

        foreach ($this->values as $name => $value) {
            $formElement = new Input($name, Input::INPUT_HIDDEN, $value);
            $formElement->setNamePrefix($this->namePrefix);
            if ($this->isAutocompleteDisabled === true) {
                $formElement->disableAutocomplete();
            }
            $innerHTML .= $formElement->parse();
        }

        $this->route->addParams(array('popup' => 1));
        $this->setAttribute('data-url', $this->route->getURL(false));
        $this->setAttribute('data-popup-selector-prefix', $this->namePrefix);
        $this->setInnerHTML($innerHTML);
        $this->addClass('reference_picker');

        return parent::parse();
    }
}
