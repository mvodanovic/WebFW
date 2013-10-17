<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Database\TableGateway;

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
        if (!in_array($formItem->getName(), $this->fieldNames)) {
            $this->fieldNames[] = $formItem->getName();
        }

        $formItem->setName(static::FIELD_PREFIX . $formItem->getName());

        if ($formItem instanceof Input && $formItem->getType() === 'hidden') {
            $this->hiddenFields[] = $formItem;
            return;
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
                    /** @var $formItem BaseFormItem */
                    $formItem = &$field['formItem'];
                    if ($formItem->getName() === static::FIELD_PREFIX . $name) {
                        if ($formItem instanceof Input) {
                            /** @var $formItem Input */
                            if ($formItem->getType() === 'checkbox' && $value === true) {
                                $formItem->setChecked();
                            } elseif ($formItem->getType() === 'radio') {
                                if ($value === $formItem->getValue()) {
                                    $formItem->setChecked();
                                }
                            } else {
                                $formItem->setValue($value);
                            }
                        } else {
                            /** @var $formItem BaseFormItem */
                            $formItem->setValue($value);
                        }
                    }
                }
            }

            foreach ($this->hiddenFields as &$formItem) {
                /** @var $formItem BaseFormItem */
                if ($formItem->getName() === static::FIELD_PREFIX . $name) {
                    if ($formItem instanceof Input) {
                        /** @var $formItem Input */
                        if ($formItem->getType() === 'checkbox' && $value === true) {
                            $formItem->setChecked();
                        } elseif ($formItem->getType() === 'radio') {
                            if ($value === $formItem->getValue()) {
                                $formItem->setChecked();
                            }
                        } else {
                            $formItem->setValue($value);
                        }
                    } else {
                        /** @var $formItem BaseFormItem */
                        $formItem->setValue($value);
                    }
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
                /** @var $formItem BaseFormItem */
                $fieldName = substr($formItem->getName(), strlen(static::FIELD_PREFIX));
                $errors = $tableGateway->getValidationErrors($fieldName);
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
        $class = $isActive ? 'button active' : 'button';

        $button = new Button(null, $this->getName(), null, 'button', $class);
        $button->setID('btn_tab_' . $this->getID());
        $button->addCustomAttribute('data-id', $this->getID());
        $button->addCustomAttribute('onclick', 'switchEditTab(this);');

        return $button->parse();
    }
}
