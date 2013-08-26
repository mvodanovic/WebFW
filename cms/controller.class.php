<?php

namespace WebFW\CMS;

use WebFW\CMS\Classes\EditAction;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\Classes\ListAction;
use WebFW\CMS\Classes\ListMassAction;
use WebFW\CMS\Classes\ListRowAction;
use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Exception;
use WebFW\Database\ListFetcher;
use WebFW\Core\Router;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\Core\Request;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\HTMLController;
use WebFW\Database\TableGateway;

abstract class Controller extends HTMLController
{
    const DEFAULT_ACTION_NAME = 'listItems';

    protected $listFetcher = null;
    protected $filter = array();
    protected $sort = array();
    protected $page = 1;
    protected $itemsPerPage = 30;
    protected $listColumns = array();
    protected $listSortingDef = null;

    protected $listFilters = array();
    protected $listActions = array();
    protected $listRowActions = array();
    protected $listMassActions = array();

    protected $editTabs = array();
    protected $editActions = array();
    protected $editForm = null;

    protected $errorMessage = null;

    protected $tableGateway = null;

    public function __construct()
    {
        parent::__construct();

        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (!LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false), true);
        }

        $this->setLinkedCSS('/static/css/reset.css');
        $this->setLinkedCSS('/static/css/formalize.css');
        $this->setLinkedCSS('/static/css/jquery-ui-1.9.1.sortable.min.css');
        $this->setLinkedCSS('/static/css/webfw/cms.css');
        $this->setLinkedJavaScript('/static/js/jquery-1.8.2.min.js');
        $this->setLinkedJavaScript('/static/js/jquery-ui-1.9.1.sortable.min.js');
        $this->setLinkedJavaScript('/static/js/webfw/cms.js');
        $this->setHtmlMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    public function listItems()
    {
        $this->initList();
        $this->checkListFetcher();
        $this->checkTableGateway();

        $this->initListFilters();
        $this->initListActions();
        $this->initListRowActions();
        $this->initListMassActions();

        $listData = $this->listFetcher->getList($this->filter, $this->sort, $this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
        $totalCount = $this->listFetcher->getCount($this->filter);

        $this->setTplVar('listData', $listData);
        $this->setTplVar('totalCount', $totalCount);
        $this->setTplVar('page', $this->page);
    }

    public function editItem()
    {
        $this->initEdit();
        $this->checkTableGateway();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);
        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (Exception $e) {
                /// TODO
                \ConsoleDebug::log($e);
            }
        }

        $this->processEdit($this->tableGateway);

        $this->initForm();
        $this->initEditActions();

        foreach ($this->editTabs as &$tab) {
            $tab->setValues($this->tableGateway->getValues(true));
        }

        $this->setTplVar('editTabs', $this->editTabs);
        $this->setTplVar('editActions', $this->editActions);
    }

    public function saveItem()
    {
        $this->initEdit();
        $this->checkTableGateway();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);

        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (Exception $e) {
                /// TODO
                throw $e;
            }
        }

        foreach ($this->editTabs as &$tab) {
            foreach ($tab->getFields() as $fieldRow) {
                foreach ($fieldRow as &$field) {
                    if ($field['formItem'] instanceof BaseFormItem) {
                        $formItemName = $field['formItem']->getName();
                        $value = Request::getInstance()->$formItemName;
                        $formItemName = substr($formItemName, strlen(EditTab::FIELD_PREFIX));
                        $this->tableGateway->$formItemName = $value;
                    }
                }
            }
        }

        $this->tableGateway->save();

        $this->setRedirectUrl($this->getURL(null, false, null, false), true);
    }

    public function deleteItem()
    {
        $this->init();
        $this->checkTableGateway();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);

        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (Exception $e) {
                /// TODO
                throw $e;
            }
        }

        $this->tableGateway->delete();

        $this->setRedirectUrl($this->getURL(null, true, null, false), true);
    }

    public function massDeleteItems()
    {
        $this->init();
        $this->checkTableGateway();

        $selectedItems = json_decode(rawurldecode(Request::getInstance()->keys), true);

        if (is_array($selectedItems)) {
            foreach ($selectedItems as &$primaryKeyValues) {
                try {
                    $this->tableGateway->loadBy($primaryKeyValues);
                } catch (Exception $e) {
                    continue;
                }
                $this->tableGateway->delete();
            }
        }

        $this->setRedirectUrl($this->getURL(null, true, null, false), true);
    }

    public function sortItems()
    {
        $this->useTemplate = false;
        $this->simpleOutput = true;
        header('Content-type: application/json; charset=UTF-8');

        $this->init();
        $this->checkTableGateway();

        $itemList = Request::getInstance()->itemList;
        if (!is_array($itemList)) {
            echo json_encode(array('status' => 'ERR'));
            return;
        }

        $orderColumn = Request::getInstance()->orderColumn;
        if ($orderColumn === null) {
            echo json_encode(array('status' => 'ERR'));
            return;
        }

        /// Each group has it's own order index range.
        /// For example, multiple users can have tasks, but ordering is important only for a single user.
        /// This assures tasks are grouped according to their user (owner).
        $groupColumns = Request::getInstance()->groupColumns;
        if (!is_array($groupColumns)) {
            /// If group columns aren't set, a default group is created for storing it's index
            $groupColumns = array('###');
        }

        /// Indexes are stored in an array
        $indexes = array();

        foreach ($itemList as $jsonPrimaryKey) {
            $primaryKey = json_decode($jsonPrimaryKey, true);
            if (!is_array($primaryKey)) {
                continue;
            }
            try {
                $this->tableGateway->loadBy($primaryKey);
            } catch (Exception $e) {
                continue;
            }

            /// Index keys are created by appending all group column values together
            $indexKey = '';
            foreach ($groupColumns as $column) {
                $indexKey .= $this->tableGateway->$column . '###';
            }

            /// Incrementation of an index for a specific group
            if (!array_key_exists($indexKey, $indexes)) {
                $indexes[$indexKey] = 0;
            } else {
                $indexes[$indexKey]++;
            }

            /// Assigning the order index to the specific item
            $this->tableGateway->$orderColumn = $indexes[$indexKey];
            $this->tableGateway->save();
        }

        echo json_encode(array('status' => 'OK'));
    }

    protected function init()
    {
        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
    }

    protected function initList()
    {
        $this->init();
        $this->template = \WebFW\Config\FW_PATH . '/cms/templates/list';
        $this->pageTitle = 'Items List';
        $this->filter += $this->getPaginatorFilter();

        $page = Request::getInstance()->p;
        if ($page !== null) {
            $this->page = $page;
        }
    }

    protected function initListActions()
    {
        /// New
        $HTMLItem = new Link('Add item', $this->getURL('editItem', false), Link::IMAGE_ADD);
        $listAction = new ListAction($HTMLItem);
        $this->registerListAction($listAction);
    }

    protected function initListRowActions()
    {
        /// Delete
        $link = new Link(null, null, Link::IMAGE_DELETE);
        $link->addCustomAttribute('onclick', "return confirm('Item will be deleted.\\nAre you sure?');");
        $route = $this->getRoute('deleteItem');
        $listRowAction = new ListRowAction($link, $route);
        $this->registerListRowAction($listRowAction);

        /// Edit
        $link = new Link(null, null, Link::IMAGE_EDIT);
        $route = $this->getRoute('editItem');
        $listRowAction = new ListRowAction($link, $route);
        $this->registerListRowAction($listRowAction);
    }

    protected function initListMassActions()
    {
        /// Delete
        $button = new Button(null, 'Delete', Button::IMAGE_DELETE, 'button', null, 'mass_delete');
        $button->addCustomAttribute('data-confirm', 'Selected items will be deleted.\\nAre you sure?');
        $button->addCustomAttribute('data-url', $this->getURL('massDeleteItems', false));
        $listMassAction = new ListMassAction($button);
        $this->registerListMassAction($listMassAction);
    }

    protected function initListFilters() {}

    protected function addListFilter(BaseFormItem $formItem, $label = null)
    {
        $name = $formItem->getName();
        if (Request::getInstance()->$name !== null) {
            $formItem->setValue(Request::getInstance()->$name);
        }
        $this->listFilters[] = array(
            'formItem' => $formItem->parse(),
            'label' => $label,
            'id' => $formItem->getID(),
        );
    }

    protected function initEdit()
    {
        $this->init();
        $this->template = \WebFW\Config\FW_PATH . '/cms/templates/edit';
        $this->pageTitle = 'Add / Edit Item';
    }

    protected function initForm()
    {
        $this->editForm = new FormStart('post', $this->getRoute('saveItem', $this->getPrimaryKeyValues()));
    }

    protected function initEditActions()
    {
        /// Save
        $HTMLItem = new Button(null, 'Save', Link::IMAGE_SAVE, 'submit');
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);

        /// Cancel
        $HTMLItem = new Link('Cancel', $this->getURL(null, false), Link::IMAGE_CANCEL);
        $HTMLItem->addCustomAttribute('onclick', "return confirm('Any unsaved changes will be lost.\\nAre you sure?');");
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);

        /// Delete
        $primaryKeyValues = $this->getPrimaryKeyValues();
        if (!empty($primaryKeyValues)) {
            $HTMLItem = new Link('Delete', $this->getURL('deleteItem'), Link::IMAGE_DELETE);
            $HTMLItem->addCustomAttribute('onclick', "return confirm('Item will be deleted.\\nAre you sure?');");
            $editAction = new EditAction($HTMLItem);
            $editAction->makeRightAligned();
            $this->registerEditAction($editAction);
        }
    }

    protected function checkListFetcher()
    {
        if (!($this->listFetcher instanceof ListFetcher)) {
            throw new Exception('Invalid listFetcher set or listFetcher not set');
        }
    }

    protected function checkTableGateway()
    {
        if (!($this->tableGateway instanceof TableGateway)) {
            throw new Exception('Invalid tableGateway set or tableGateway not set');
        }
    }

    protected function addListColumn($key, $caption, $shrinked = false)
    {
        $this->listColumns[] = array(
            'key' => $key,
            'caption' => $caption,
            'shrinked' => $shrinked,
        );
    }

    public function processList(&$list) {}

    public function processEdit(TableGateway &$item) {}

    public function getListFetcher()
    {
        return $this->listFetcher;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function getListColumns()
    {
        return $this->listColumns;
    }

    public function getListActions()
    {
        return $this->listActions;
    }

    public function getListRowActions()
    {
        return $this->listRowActions;
    }

    public function getListMassActions()
    {
        return $this->listMassActions;
    }

    public function getListFilters()
    {
        return $this->listFilters;
    }

    public function getSelectedMenuItem()
    {
        return strtolower(str_replace('\\', '_', get_class($this)));
    }

    public function getPrimaryKeyColumns()
    {
        return $this->tableGateway->getTable()->getPrimaryKeyColumns();
    }

    public function getControllerName()
    {
        return $this->ctl;
    }

    public function getNamespace()
    {
        return $this->ns;
    }

    public function getPaginatorFilter()
    {
        return Request::getInstance()->getValuesWithPrefix('f_');
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function registerEditAction(EditAction $action)
    {
        $this->editActions[] = $action;
    }

    public function registerListAction(ListAction $action)
    {
        $this->listActions[] = $action;
    }

    public function clearListActions()
    {
        $this->listActions = array();
    }

    public function registerListRowAction(ListRowAction $action)
    {
        $this->listRowActions[] = $action;
    }

    public function clearListRowActions()
    {
        $this->listRowActions = array();
    }

    public function registerListMassAction(ListMassAction $action)
    {
        $this->listMassActions[] = $action;
    }

    public function clearListMassActions()
    {
        $this->listMassActions = array();
    }

    public function clearEditActions()
    {
        $this->editActions = array();
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

    public function getPrimaryKeyValues($keepPrefix = true)
    {
        return Request::getInstance()->getValuesWithPrefix('pk_', $keepPrefix);
    }

    public function getEditRequestValues()
    {
        return Request::getInstance()->getValuesWithPrefix(EditTab::FIELD_PREFIX, false);
    }

    public function getEditFormHTML()
    {
        return $this->editForm->parse();
    }

    public function enableListSorting($handlerFunction, $orderColumn, $groupColumns = array())
    {
        $this->listSortingDef = array(
            'url' => $this->getURL($handlerFunction, false, null, false),
            'orderColumn' => $orderColumn,
            'groupColumns' => $groupColumns,
        );
    }

    public function isSortingEnabled()
    {
        return $this->listSortingDef !== null;
    }

    public function getSortingDef()
    {
        return $this->listSortingDef;
    }

    public function getJSONSortingDef()
    {
        return json_encode($this->listSortingDef);
    }

    public static function getBooleanPrint($boolean)
    {
        switch (true) {
            case $boolean === true:
            case $boolean === 1:
            case $boolean === '1':
            case $boolean === 't':
                return 'true';
            case $boolean === false:
            case $boolean === 0:
            case $boolean === '0':
            case $boolean === 'f':
                return 'false';
            default:
                return 'n/a';
        }
    }
}
