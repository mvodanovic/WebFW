<?php

namespace WebFW\Framework\Core\Classes\HTML;

use WebFW\Framework\Core\Classes\HTML\Base\CompoundFormItem;
use WebFW\Framework\Core\Classes\HTML\Base\GeneralHTMLItem;
use WebFW\Framework\Core\Exception;
use WebFW\Framework\Core\Exceptions\NotFoundException;
use WebFW\Framework\Core\Route;
use WebFW\Framework\Database\TableColumns\Column;
use WebFW\Framework\Database\TableConstraints\ForeignKey;
use WebFW\Framework\Database\TableGateway;

class ReferencedListPicker extends CompoundFormItem
{
    protected $route;
    protected $foreignKey;
    protected $tableGatewayName;
    protected $captions = array();

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
            $this->values[$column->getName()] = array();
        }

        parent::__construct();
    }

    protected function getCaption($index)
    {
        if (!array_key_exists($index, $this->captions)) {
            /** @var TableGateway $tableGateway */
            $tableGateway = new $this->tableGatewayName();
            $unique = array();
            foreach ($this->values as $name => &$valueArray) {
                $unique[$name] = $valueArray[$index];
            }
            try {
                $tableGateway->loadBy($unique);
                $this->captions[$index] = $tableGateway->getCaption();
            } catch (NotFoundException $e) {}
        }

        return array_key_exists($index, $this->captions) ? $this->captions[$index] : null;
    }

    public function setValue($name, $value)
    {
        if (array_key_exists($name, $this->values)) {
            $this->values[$name][] = $value;
        }
    }

    public function parse()
    {
        $innerHTML = '';
        $key = key($this->values);
        if ($key !== null) {
            foreach ($this->values[$key] as $i => $value) {
                $formElement = new GeneralHTMLItem('span');
                $formElement->addClass('caption');
                $formElement->setStyle('display', 'inline-block');
                $formElement->setStyle('margin-right', '0.3em');
                $formElement->setInnerHTML(htmlspecialchars($this->getCaption($i)));
                $rowHTML = $formElement->parse();

                $formElement = new Button(
                    null,
                    Button::BUTTON_BUTTON,
                    array('icons' => array('primary' => 'ui-icon-pencil'), 'text' => false)
                );
                $formElement->addClass('select');
                $formElement->setStyle('margin-right', '0.3em');
                $rowHTML .= $formElement->parse();

                $formElement = new Button(
                    null,
                    Button::BUTTON_BUTTON,
                    array('icons' => array('primary' => 'ui-icon-close'), 'text' => false)
                );
                $formElement->addClass('clear');
                $rowHTML .= $formElement->parse();

                foreach ($this->values as $name => &$valueArray) {
                    $value = $this->values[$name][$i];
                    $formElement = new Input($name . '[]', Input::INPUT_HIDDEN, $value);
                    $formElement->setNamePrefix($this->namePrefix);
                    if ($this->isAutocompleteDisabled === true) {
                        $formElement->disableAutocomplete();
                    }
                    $rowHTML .= $formElement->parse();
                }

                $formElement = new GeneralHTMLItem();
                $formElement->addClass('row');
                $formElement->setInnerHTML($rowHTML);
                $innerHTML .= $formElement->parse();
            }
        }

        $formElement = new Button(
            null,
            Button::BUTTON_BUTTON,
            array('icons' => array('primary' => 'ui-icon-plusthick'), 'text' => false)
        );
        $formElement->addClass('new');
        $formElement->setStyle('margin-right', '0.3em');
        $rowHTML = $formElement->parse();

        $formElement = new GeneralHTMLItem();
        $formElement->setInnerHTML($rowHTML);
        $innerHTML .= $formElement->parse();

        $this->route->addParams(array('popup' => 1));
        $this->setAttribute('data-url', $this->route->getURL(false));
        $this->setAttribute('data-popup-selector-prefix', $this->namePrefix);
        $this->setInnerHTML($innerHTML);
        $this->addClass('referenced_list_picker');

        return parent::parse();
    }
}
