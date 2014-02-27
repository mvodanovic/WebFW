<?php

namespace mvodanovic\WebFW\CMS;

use mvodanovic\WebFW\CMS\Classes\EditAction;
use mvodanovic\WebFW\CMS\Classes\ListAction;
use mvodanovic\WebFW\CMS\Classes\ListMassAction;
use mvodanovic\WebFW\CMS\Classes\ListRowAction;
use mvodanovic\WebFW\CMS\Classes\PermissionsHelper;
use mvodanovic\WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use mvodanovic\WebFW\Core\Classes\HTML\Base\BaseFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Base\CompoundFormItem;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Core\Exceptions\BadRequestException;
use mvodanovic\WebFW\Core\Exceptions\UnauthorizedException;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Core\Request;
use mvodanovic\WebFW\Core\Classes\HTML\Link;
use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;
use mvodanovic\WebFW\Core\Classes\HTML\Button;
use mvodanovic\WebFW\Database\TableGateway;

abstract class ListController extends ItemController
{
    const DEFAULT_ACTION_NAME = 'listItems';
    const LIST_FILTER_PREFIX = 'f_';

    /** @var ListFetcher */
    protected $listFetcher = null;
    protected $sort = array();
    protected $page = 1;
    protected $itemsPerPage = 30;
    protected $listColumns = array();
    protected $listSortingDef = null;

    protected $listFilters = array();
    protected $listActions = array();
    protected $listRowActions = array();
    protected $listMassActions = array();

    public function listItems()
    {
        if (!PermissionsHelper::checkForController($this, UTCP::TYPE_SELECT)) {
            throw new UnauthorizedException('Insufficient privileges');
        }

        $this->initList();
        $this->checkListFetcher();
        $this->checkTableGateway();
        $this->afterInit();
        $this->afterInitList();

        $this->initListFilters();
        $this->initListActions();
        $this->initListRowActions();
        $this->initListMassActions();

        $listData = $this->listFetcher->getList(
            $this->filter,
            $this->sort,
            $this->itemsPerPage,
            ($this->page - 1) * $this->itemsPerPage
        );
        $totalCount = $this->listFetcher->getCount($this->filter);

        $this->setTplVar('listData', $listData);
        $this->setTplVar('totalCount', $totalCount);
        $this->setTplVar('page', $this->page);
    }

    public function deleteItem()
    {
        $this->init();
        $this->checkTableGateway();
        $this->afterInit();

        if (!PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            throw new UnauthorizedException('Insufficient privileges');
        }

        $primaryKeyValues = $this->getPrimaryKeyValues(false);

        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (NotFoundException $e) {
                $key = json_encode($primaryKeyValues);
                throw new BadRequestException('No data to delete with the following key: ' . $key, $e);
            }
        }

        $this->beforeDelete();
        $this->tableGateway->delete();
        $this->afterDelete();

        $this->setRedirectUrl($this->getURL(null, true, null, false), true);
    }

    public function massDeleteItems()
    {
        $this->init();
        $this->checkTableGateway();
        $this->afterInit();

        if (!PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            throw new UnauthorizedException('Insufficient privileges');
        }

        $selectedItems = json_decode(rawurldecode(Request::getInstance()->keys), true);

        if (is_array($selectedItems)) {
            foreach ($selectedItems as &$primaryKeyValues) {
                try {
                    $this->tableGateway->loadBy($primaryKeyValues);
                } catch (NotFoundException $e) {
                    continue;
                }

                $this->beforeDelete();
                $this->tableGateway->delete();
                $this->afterDelete();
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
        $this->afterInit();

        if (!PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            echo json_encode(array('status' => 'Insufficient privileges!'));
            return;
        }

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
            } catch (NotFoundException $e) {
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

    protected function initList()
    {
        $this->init();
        $this->template = 'list';
        $this->filter += $this->getFilterValues();

        $page = Request::getInstance()->p;
        if ($page !== null) {
            $this->page = $page;
        }
    }

    protected function initListActions()
    {
        /// New
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-plusthick'),
                'label' => 'Add item',
            );
            $HTMLItem = new Link(null, $this->getURL('editItem', false, null, false), $options);
            $listAction = new ListAction($HTMLItem);
            $this->registerListAction($listAction);
        }
    }

    protected function initListRowActions()
    {
        /// Delete
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-trash'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $link->setEvent('click', 'confirmAction', array('message' => "Item will be deleted.\nAre you sure?"));
            $route = $this->getRoute('deleteItem');
            $listRowAction = new ListRowAction($link, $route);
            $this->registerListRowAction($listRowAction);
        }

        /// Edit
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-pencil'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $route = $this->getRoute('editItem');
            $listRowAction = new ListRowAction($link, $route);
            $this->registerListRowAction($listRowAction);
        }

        /// Select
        if ($this->isPopup === true) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-check'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $link->addClass('reference_select');
            $listRowAction = new ListRowAction($link, null, true);
            $this->registerListRowAction($listRowAction);
        }
    }

    protected function initListMassActions()
    {
        /// Delete
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-trash'),
                'label' => 'Delete',
            );
            $button = new Button(null, Button::BUTTON_BUTTON, $options);
            $button->addClass('mass_delete');
            $button->setEvent(
                'click',
                'confirmAction',
                array('message' => "Selected items will be deleted.\nAre you sure?")
            );
            $button->setAttribute('data-url', $this->getURL('massDeleteItems', false, null, false));
            $listMassAction = new ListMassAction($button);
            $this->registerListMassAction($listMassAction);
        }
    }

    protected function initListFilters() {}

    protected function addListFilter(BaseFormItem $formItem, $label = null)
    {
        $formItem->setNamePrefix(static::LIST_FILTER_PREFIX);
        $formItem->disableAutocomplete();

        if ($formItem instanceof SimpleFormItem) {
            $prefixedName = static::LIST_FILTER_PREFIX . $formItem->getName();
            $value = $this->tableGateway->castValueToColumnType(
                $formItem->getName(),
                Request::getInstance()->$prefixedName
            );
            if ($value !== null) {
                $formItem->setValue($value);
            }
        } elseif ($formItem instanceof CompoundFormItem) {
            foreach ($formItem->getNames() as $name) {
                $prefixedName = static::LIST_FILTER_PREFIX . $name;
                $value = $this->tableGateway->castValueToColumnType($name, Request::getInstance()->$prefixedName);
                if ($value !== null) {
                    $formItem->setValue($name, $value);
                }
            }
        }

        $this->listFilters[] = array(
            'formItem' => $formItem,
            'label' => $label,
        );
    }

    protected function initEditActions()
    {
        parent::initEditActions();

        $primaryKeyValues = $this->getPrimaryKeyValues();

        /// Delete
        if (!empty($primaryKeyValues) && PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-trash'),
                'label' => 'Delete',
            );
            $HTMLItem = new Link(null, $this->getURL('deleteItem', true, null, false), $options);
            $HTMLItem->setEvent('click', 'confirmDeleteInEdit', array('message' => "Item will be deleted.\nAre you sure?"));
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

    protected function addListColumn($key, $caption, $shrinked = false)
    {
        $this->listColumns[] = array(
            'key' => $key,
            'caption' => $caption,
            'shrinked' => $shrinked,
        );
    }

    public function processList(&$list) {}

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

    public function getFilterValues($keepPrefix = false)
    {
        return Request::getInstance()->getValuesWithPrefix(static::LIST_FILTER_PREFIX, $keepPrefix);
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

    public function getPrimaryKeyValues($keepPrefix = true)
    {
        return Request::getInstance()->getValuesWithPrefix(TableGateway::PRIMARY_KEY_PREFIX, $keepPrefix);
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

    protected function setPageTitle()
    {
        parent::setPageTitle();

        $actionName = '';
        switch ($this->action) {
            case 'listItems':
                $actionName = 'Items List - ';
                break;
            case 'editItem':
                $actionName = 'Item Editor - ';
                break;
        }

        $this->pageTitle = $actionName . $this->pageTitle;
    }
}
