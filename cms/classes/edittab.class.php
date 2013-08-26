<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Input;

class EditTab
{
    protected $ID;
    protected $name;
    protected $fields = array();
    protected $hiddenFields = array();
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

    public function setValues($values)
    {
        foreach ($values as $name => $value) {
            foreach ($this->fields as &$fieldRow) {
                foreach ($fieldRow as &$field) {
                    $formItem = &$field['formItem'];
                    if ($formItem->getName() === static::FIELD_PREFIX . $name) {
                        if ($formItem instanceof Input) {
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
                            $formItem->setValue($value);
                        }
                    }
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

    public function getFields()
    {
        return $this->fields;
    }

    public function getHiddenFields()
    {
        return $this->hiddenFields;
    }
}
