<?php

namespace WebFW\CMS;

use WebFW\CMS\Classes\EditAction;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\Classes\PermissionsHelper;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Core\Exceptions\UnauthorizedException;
use WebFW\Core\Exception;
use WebFW\Core\Interfaces\iValidate;
use WebFW\Core\SessionHandler;
use WebFW\Core\Request;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Database\TableGateway;

abstract class ItemController extends Controller implements iValidate
{
    const DEFAULT_ACTION_NAME = 'editItem';

    protected $editTabs = array();
    protected $editActions = array();
    protected $editForm = null;

    protected $tableGateway = null;
    protected $unsavedChangesExistMessage = 'Unsaved changes exist. Are you sure you want to leave?';

    public function editItem()
    {
        if (!PermissionsHelper::checkForController($this, UTCP::TYPE_SELECT)) {
            throw new UnauthorizedException('Insufficient privileges');
        }

        $this->initEdit();
        $this->checkTableGateway();
        $this->afterInit();
        $this->afterInitEdit();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);
        if (!empty($primaryKeyValues)) {
            $this->beforeLoad();
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (NotFoundException $e) {
                /// TODO
            }
            $this->afterLoad();
        }

        foreach ($this->getEditRequestValues() as $key => $value) {
            $this->tableGateway->$key = $value;
        }

        $validationErrors = $this->retrieveValidationErrors();
        foreach ($validationErrors as $field => $errors) {
            foreach ($errors as $error) {
                $this->addValidationError($field, $error);
            }
        }
        if ($this->hasValidationErrors()) {
            $this->addMessage(new Message('Input errors present!', Message::TYPE_ERROR));
        }

        if (empty($this->editTabs)) {
            $this->editTabs[] = new EditTab('auto');
        }

        $this->processEdit($this->tableGateway);

        $this->initForm();
        $this->initEditActions();

        foreach ($this->editTabs as &$tab) {
            $tab->setValues($this->tableGateway->getValues(true));
            $tab->setErrors($this->tableGateway);
        }

        $this->setTplVar('editTabs', $this->editTabs);
        $this->setTplVar('editActions', $this->editActions);
    }

    public function saveItem()
    {
        $this->initEdit();
        $this->checkTableGateway();
        $this->afterInit();
        $this->afterInitEdit();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);

        if (!empty($primaryKeyValues)) {
            if (!PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
                throw new UnauthorizedException('Insufficient privileges');
            }
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (NotFoundException $e) {
                /// TODO
                throw $e;
            }
        } else {
            if (!PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
                throw new UnauthorizedException('Insufficient privileges');
            }
        }

        foreach ($this->filter as $column => $value)
        {
            $this->tableGateway->$column = $value;
        }

        foreach ($this->editTabs as &$tab) {
            foreach ($tab->getFields() as $fieldRow) {
                foreach ($fieldRow as &$field) {
                    if ($field['formItem'] instanceof BaseFormItem) {
                        $formItemName = $field['formItem']->getName();
                        $value = Request::getInstance()->$formItemName;
                        $formItemName = substr($formItemName, strlen(EditTab::FIELD_PREFIX));
                        /// If checkbox is left empty, it's value is FALSE, and not NULL.
                        if (
                            $value === null
                            && $field['formItem'] instanceof Input
                            && $field['formItem']->getType() == 'checkbox'
                        ) {
                            $value = false;
                        }
                        $this->tableGateway->$formItemName = $value;
                    }
                }
            }
        }

        $this->beforeSave();
        $this->validateData();
        $this->tableGateway->save();
        if ($this->hasValidationErrors()) {
            $this->storeValidationErrors($this->getValidationErrors());
            $this->storeFieldValues(Request::getInstance()->getValuesWithPrefix(EditTab::FIELD_PREFIX, false));
            $this->setRedirectUrl($this->getURL('editItem', true, null, false), true);
        }
        $this->afterSave();

        $this->setRedirectUrl($this->getURL(null, false, null, false), true);
    }

    protected function initEdit()
    {
        $this->init();
        $this->template = \WebFW\Core\FW_PATH . '/cms/templates/edit';
    }

    protected function afterInitEdit()
    {
        parent::afterInitEdit();

        if ($this->unsavedChangesExistMessage !== null) {
            $this->addHeadJS(
                'var unsavedChangesExistMessage = "' . htmlspecialchars($this->unsavedChangesExistMessage) . '";'
            );
        }
    }

    protected function initForm()
    {
        $primaryKeyValues = $this->getPrimaryKeyValues();
        if (empty($primaryKeyValues) && PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)
            || PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            $this->editForm = new FormStart('post', $this->getRoute('saveItem', $this->getPrimaryKeyValues()));
            $this->editForm->addCustomAttribute('onsubmit', 'return beforeSubmitEdit();');
        }
    }

    protected function initEditActions()
    {
        $primaryKeyValues = $this->getPrimaryKeyValues();

        /// Save
        if (empty($primaryKeyValues) && PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
            $HTMLItem = new Button(null, 'Save new', Link::IMAGE_SAVE, 'submit');
            $editAction = new EditAction($HTMLItem);
            $this->registerEditAction($editAction);
        } elseif (PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            $HTMLItem = new Button(null, 'Update', Link::IMAGE_SAVE, 'submit');
            $editAction = new EditAction($HTMLItem);
            $this->registerEditAction($editAction);
        }

        /// Cancel
        $HTMLItem = new Link('Cancel', $this->getURL(null, false, null, false), Link::IMAGE_CANCEL);
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);
    }

    protected function checkTableGateway()
    {
        if (!($this->tableGateway instanceof TableGateway)) {
            throw new Exception('Invalid tableGateway set or tableGateway not set');
        }
    }

    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    public function processEdit(TableGateway &$item) {}

    public function getPrimaryKeyColumns()
    {
        return $this->tableGateway->getTable()->getPrimaryKeyColumns();
    }

    public function registerEditAction(EditAction $action)
    {
        $this->editActions[] = $action;
    }

    public function clearEditActions()
    {
        $this->editActions = array();
    }

    public function getEditRequestValues()
    {
        $values = $this->retrieveFieldValues();
        if ($values === null) {
            $values = Request::getInstance()->getValuesWithPrefix(EditTab::FIELD_PREFIX, false);
        }

        return $values;
    }

    public function getEditFormHTML()
    {
        return $this->editForm === null ? null : $this->editForm->parse();
    }

    public function getURL($action, $setPrimaryKey = true, $additionalParams = null, $escapeAmps = true, $rawurlencode = true)
    {
        if ($additionalParams === null) {
            $additionalParams = array();
        }

        if ($setPrimaryKey === true) {
            $additionalParams += $this->getPrimaryKeyValues();
        }

        return parent::getURL($action, $additionalParams, $escapeAmps, $rawurlencode);
    }

    abstract public function getPrimaryKeyValues($keepPrefix = true);

    protected function beforeLoad() {}
    protected function afterLoad () {}
    protected function beforeSave() {}
    protected function afterSave() {}
    protected function beforeDelete() {}
    protected function afterDelete() {}

    public function validateData()
    {
    }

    public function addValidationError($field, $error)
    {
        if ($this->tableGateway instanceof iValidate) {
            $this->tableGateway->addValidationError($field, $error);
        }
    }

    public function hasValidationErrors()
    {
        if ($this->tableGateway instanceof iValidate) {
            return $this->tableGateway->hasValidationErrors();
        }
    }

    public function getValidationErrors($field = null)
    {
        if ($this->tableGateway instanceof iValidate) {
            return $this->tableGateway->getValidationErrors($field);
        }
    }

    public function clearValidationErrors()
    {
        if ($this->tableGateway instanceof iValidate) {
            $this->tableGateway->clearValidationErrors();
        }
    }

    protected function storeValidationErrors($errors)
    {
        SessionHandler::set($this->getSessionKey('validate'), $errors);
    }

    protected function retrieveValidationErrors()
    {
        $errors = SessionHandler::get($this->getSessionKey('validate'));
        SessionHandler::kill($this->getSessionKey('validate'));
        if (!is_array($errors)) {
            $errors = array();
        }
        return $errors;
    }

    protected function storeFieldValues($values)
    {
        SessionHandler::set($this->getSessionKey('fields'), $values);
    }

    protected function retrieveFieldValues()
    {
        $values = SessionHandler::get($this->getSessionKey('fields'));

        if (is_array($values)) {
            SessionHandler::kill($this->getSessionKey('fields'));
            foreach ($this->tableGateway->getValues() as $key => $value) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = null;
                }
            }
        }
        return $values;
    }

    protected function getSessionKey($operation)
    {
        return 'webfw-' . $operation . '-' . $this->ns . $this->ctl;
    }
}
