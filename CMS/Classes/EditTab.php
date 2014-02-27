<?php

namespace mvodanovic\WebFW\CMS\Classes;

use mvodanovic\WebFW\CMS\ItemController;
use mvodanovic\WebFW\Core\Classes\HTML\Base\BaseFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Base\CompoundFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Button;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Controller;
use mvodanovic\WebFW\Database\TableGateway;

class EditTab
{
    protected $ID;
    protected $name;
    protected $fields = array();
    protected $hiddenFields = array();
    protected $fieldNames = array();
    protected $currentLineIndex = null;
    protected $extendedRowspanFields = array();

    const FIELD_PREFIX = 'edit_';

    public function __construct($ID, $name = null)
    {
        $this->ID = $ID;
        $this->name = $name === null ? $ID : $name;
    }

    public function addField(BaseFormItem $formItem, $label, $description = null, $newLine = true, $rowspan = 1, $colspan = 1)
    {
        $formItem->setNamePrefix(static::FIELD_PREFIX);
        $formItem->disableAutocomplete();
        if ($formItem instanceof SimpleFormItem) {
            if (!in_array($formItem->getName(), $this->fieldNames)) {
                $this->fieldNames[] = $formItem->getName();
            }
        } elseif ($formItem instanceof CompoundFormItem) {
            foreach ($formItem->getNames() as $name) {
                if (!in_array($name, $this->fieldNames)) {
                    $this->fieldNames[] = $name;
                }
            }
        }

        if ($formItem instanceof Input && $formItem->getType() === Input::INPUT_HIDDEN) {
            $this->hiddenFields[] = $formItem;
            return;
        } elseif ($formItem instanceof Input && $formItem->getType() === Input::INPUT_FILE) {
            /** @var ItemController $controller */
            $controller = Controller::getInstance();
            $controller->setFileUploadEditForm();
        }

        if ($newLine === true) {
            $this->currentLineIndex = null;
        }

        if ($this->currentLineIndex === null) {
            $this->currentLineIndex = $this->getNewLineIndex();
        }

        if ($description !== null) {
            $description = Tooltip::get($description, Tooltip::TYPE_NOTICE);
        }

        $this->fields[$this->currentLineIndex][] = array(
            'formItem' => $formItem,
            'label' => $label,
            'description' => $description,
            'rowspan' => $rowspan,
            'colspan' => $colspan,
            'rowspanFix' => false,
        );

        $this->setRowspanFix();

        if ($rowspan > 1) {
            end($this->fields[$this->currentLineIndex]);
            $currentColumnIndex = key($this->fields[$this->currentLineIndex]);
            reset($this->fields[$this->currentLineIndex]);
            $this->extendedRowspanFields[] = array(
                'x' => $this->currentLineIndex,
                'y' => $currentColumnIndex,
                'h' => $rowspan,
            );
        }
    }

    public function hasField($fieldName)
    {
        return in_array($fieldName, $this->fieldNames);
    }

    /**
     * @param array $values
     */
    public function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            foreach ($this->fields as &$fieldRow) {
                foreach ($fieldRow as &$field) {
                    $formItem = &$field['formItem'];
                    if ($formItem instanceof SimpleFormItem) {
                        if ($formItem->getName() === $name) {
                            if ($formItem instanceof Input
                                    && in_array($formItem->getType(), array(Input::INPUT_CHECKBOX, Input::INPUT_RADIO))
                                    && $value === true
                            ) {
                                $formItem->setChecked();
                            } else {
                                $formItem->setValue($value);
                            }
                        }
                    } elseif ($formItem instanceof CompoundFormItem) {
                        if (in_array($name, $formItem->getNames())) {
                            $formItem->setValue($name, $value);
                        }
                    }
                }
            }

            foreach ($this->hiddenFields as &$formItem) {
                /** @var $formItem Input */
                if ($formItem->getName() === $name) {
                    $formItem->setValue($value);
                }
            }
        }
    }

    /**
     * @param TableGateway $tableGateway
     */
    public function setErrors(TableGateway $tableGateway)
    {
        foreach ($this->fields as &$fieldRow) {
            foreach ($fieldRow as &$field) {
                $formItem = &$field['formItem'];
                $errors = array();
                if ($formItem instanceof SimpleFormItem) {
                    $errors = $tableGateway->getValidationErrors($formItem->getName());
                } elseif ($formItem instanceof CompoundFormItem) {
                    $errors = array();
                    foreach ($formItem->getNames() as $name) {
                        $errors = array_merge($errors, $tableGateway->getValidationErrors($name));
                    }
                }
                if (!empty($errors)) {
                    $field['error'] = '';
                }
                foreach ($errors as $error) {
                    $field['error'] .= Tooltip::get($error, Tooltip::TYPE_ERROR);
                }
            }
        }
    }

    protected function getNewLineIndex()
    {
        end($this->fields);
        $key = key($this->fields);
        reset($this->fields);

        if ($key === null) {
            $key = 0;
        } else {
            $key++;
        }
        $this->fields[$key] = array();

        return $key;
    }

    protected function setRowspanFix()
    {
        end($this->fields[$this->currentLineIndex]);
        $currentColumnIndex = key($this->fields[$this->currentLineIndex]);
        reset($this->fields[$this->currentLineIndex]);
        foreach ($this->extendedRowspanFields as &$fieldDef) {
            if ($fieldDef['y'] !== $currentColumnIndex + 1) {
                continue;
            }
            if ($fieldDef['x'] + $fieldDef['h'] - 1 > $this->currentLineIndex) {
                continue;
            }

            $this->fields[$this->currentLineIndex][$currentColumnIndex]['rowspanFix'] = true;
            break;
        }
    }

    public function getID()
    {
        return $this->ID;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFieldCount()
    {
        return count($this->fields);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getHiddenFieldCount()
    {
        return count($this->hiddenFields);
    }

    public function getHiddenFields()
    {
        return $this->hiddenFields;
    }

    public function getButton($isActive = false)
    {
        $class = $isActive ? ' ui-state-active ui-state-persist' : null;

        $button = new Button($this->getName());
        $button->addClass($class);
        $button->setAttribute('data-id', $this->getID());
        $button->setEvent('click', 'switchEditTab', array('id' => $this->getID()));

        return $button->parse();
    }
}
