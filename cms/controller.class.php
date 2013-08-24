<?php

namespace WebFW\CMS;

use \WebFW\CMS\Classes\EditAction;
use \WebFW\CMS\Classes\ListAction;
use \WebFW\CMS\Classes\ListMassAction;
use \WebFW\CMS\Classes\ListRowAction;
use \WebFW\Core\Classes\HTML\FormStart;
use \WebFW\Core\Exception;
use \WebFW\Database\ListFetcher;
use \WebFW\Core\Router;
use \WebFW\CMS\Classes\LoggedUser;
use \WebFW\Core\Request;
use \WebFW\Core\Classes\HTML\Link;
use \WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use \WebFW\Core\Classes\HTML\Base\BaseFormItem;
use \WebFW\Core\Classes\HTML\Button;
use \WebFW\Core\HTMLController;

abstract class Controller extends HTMLController
{
    const DEFAULT_ACTION_NAME = 'listItems';

    protected $listFetcher = null;
    protected $filter = array();
    protected $sort = array();
    protected $page = 1;
    protected $itemsPerPage = 30;
    protected $listColumns = array();
    protected $listHeaderButtons = array();
    protected $listRowButtons = array();
    protected $listFooterButtons = array();
    protected $listHasCheckboxes = false;
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

        $listData = $this->listFetcher->getList($this->filter, $this->sort, $this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
        $totalCount = $this->listFetcher->getCount($this->filter);

        $this->setTplVar('listData', $listData);
        $this->setTplVar('totalCount', $totalCount);
        $this->setTplVar('page', $this->page);
    }

    public function editItem()
    {
        $this->initEdit();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);
        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (Exception $e) {
                /// TODO
                \ConsoleDebug::log($e);
            }
        }

        foreach ($this->editTabs as $tab) {
            $tab->setValues($this->tableGateway->getValues());
        }

        $this->setTplVar('editTabs', $this->editTabs);
        $this->setTplVar('editActions', $this->editActions);
    }

    public function saveItem()
    {
        $this->init();

        $primaryKeyValues = $this->getPrimaryKeyValues(false);

        if (!empty($primaryKeyValues)) {
            try {
                $this->tableGateway->loadBy($primaryKeyValues);
            } catch (Exception $e) {
                /// TODO
                throw $e;
            }
        }

        $values = $this->getEditRequestValues();
        foreach ($values as $key => $value) {
            $this->tableGateway->$key = $value;
        }

        $this->tableGateway->save();

        $this->setRedirectUrl($this->getURL('listItems', true, null, false), true);
    }

    public function deleteItem()
    {
        $this->init();

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

        $this->setRedirectUrl($this->getURL('listItems', true, null, false), true);
    }

    public function massDeleteItems()
    {
        $this->init();

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

        $this->setRedirectUrl($this->getURL('listItems', true, null, false), true);
    }

    protected function init()
    {
        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
    }

    protected function initList()
    {
        $this->init();
        $this->initListFilters();
        $this->initListActions();
        $this->initListRowActions();
        $this->initListMassActions();
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
//        $this->listHasCheckboxes = true;

//        $this->addListHeaderAction(
//            new Link('Add item', null, Link::IMAGE_ADD),
//            new Route($this->ctl, 'editItem', $this->ns)
//        );

        $HTMLItem = new Link('Add item', $this->getURL('editItem', false), Link::IMAGE_ADD);
        $listAction = new ListAction($HTMLItem);
        $this->registerListAction($listAction);

//        $this->addListRowAction(
//            new Link(null, null, Link::IMAGE_DELETE),
//            new Route($this->ctl, 'deleteItem', $this->ns)
//        );

//        $this->addListRowAction(
//            new Link(null, null, Link::IMAGE_EDIT),
//            new Route($this->ctl, 'editItem', $this->ns)
//        );

        /*
        $deleteButton = new Button(null, 'Delete', Link::IMAGE_DELETE);
        $deleteButton->addCustomAttribute('type', 'submit');
        $this->addListFooterButton($deleteButton);
        $this->addListFooterButton(
            new Link('Delete', null, Link::IMAGE_DELETE),
            new Route($this->ctl, 'deleteItem', $this->ns)
        );
        */
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

    protected function addListHeaderAction(BaseHTMLItem $button, $link = null)
    {
        $this->listHeaderButtons[] = array(
            'button' => $button,
            'link' => $link,
        );
    }

    protected function addListRowAction(BaseHTMLItem $button, $link = null)
    {
        $this->listRowButtons[] = array(
            'button' => $button,
            'link' => $link,
        );
    }

    protected function addListFooterAction(BaseHTMLItem $button, $link = null)
    {
        $this->listFooterButtons[] = array(
            'button' => $button,
            'link' => $link,
        );
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
        $this->initForm();
        $this->initEditActions();
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
        $HTMLItem = new Link('Cancel', $this->getURL('listItems', false), Link::IMAGE_CANCEL);
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

    public function getListHeaderButtons()
    {
        return $this->listHeaderButtons;
    }
    public function getListActions()
    {
        return $this->listActions;
    }

    public function getListRowButtons()
    {
        return $this->listRowButtons;
    }
    public function getListRowActions()
    {
        return $this->listRowActions;
    }

    public function getListFooterButtons()
    {
        return $this->listFooterButtons;
    }
    public function getListMassActions()
    {
        return $this->listMassActions;
    }

    public function getListHasCheckboxes()
    {
        return $this->listHasCheckboxes;
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
        $this->listHasCheckboxes = true;
    }

    public function clearListMassActions()
    {
        $this->listMassActions = array();
        $this->listHasCheckboxes = false;
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
        return Request::getInstance()->getValuesWithPrefix('edit_', false);
    }

    public function getEditFormHTML()
    {
        return $this->editForm->parse();
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
