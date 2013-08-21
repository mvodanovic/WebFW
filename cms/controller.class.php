<?php

namespace WebFW\CMS;

use \WebFW\Core\Exception;
use \WebFW\Database\ListFetcher;
use \WebFW\Core\Router;
use \WebFW\CMS\Classes\LoggedUser;
use \WebFW\CMS\Classes\EditTab;
use \WebFW\Core\Request;
use \WebFW\Core\Route;
use \WebFW\Core\Classes\HTML\Link;
use \WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use \WebFW\Core\Classes\HTML\Base\BaseFormItem;
use \WebFW\Core\Classes\HTML\Button;

abstract class Controller extends \WebFW\Core\HTMLController
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

    protected $editTabs = array();

    protected $ctl;
    protected $ns;
    protected $errorMessage = null;

    protected $tableGateway = null;

    public function __construct()
    {
        parent::__construct();

        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (!LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false), true);
        }

        $separator = strrpos($this->className, '\\') + 1;
        $this->ns = '\\' . substr($this->className, 0, $separator);
        $this->ctl = substr($this->className, $separator);

        $page = Request::getInstance()->p;
        if ($page !== null) {
            $this->page = $page;
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

        $primaryKeyValues = Request::getInstance()->getValuesWithPrefix('pk_', false);
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
    }

    protected function init()
    {
        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
    }

    protected function initList()
    {
        $this->init();
        $this->initListFilters();
        $this->initListButtons();
        $this->template = \WebFW\Config\FW_PATH . '/cms/templates/list';
        $this->pageTitle = 'Items List';
        $this->filter += Request::getInstance()->getValuesWithPrefix('f_', false);
    }

    protected function initListButtons()
    {
        $this->listHasCheckboxes = true;
        $this->addListHeaderButton(
            new Link('Add item', null, Link::IMAGE_ADD),
            new Route($this->ctl, 'editItem', $this->ns)
        );
        $this->addListRowButton(
            new Link(null, null, Link::IMAGE_DELETE),
            new Route($this->ctl, 'deleteItem', $this->ns)
        );
        $this->addListRowButton(
            new Link(null, null, Link::IMAGE_EDIT),
            new Route($this->ctl, 'editItem', $this->ns)
        );
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

    protected function addListHeaderButton(BaseHTMLItem $button, $link = null)
    {
        $this->listHeaderButtons[] = array(
            'button' => $button,
            'link' => $link,
        );
    }

    protected function addListRowButton(BaseHTMLItem $button, $link = null)
    {
        $this->listRowButtons[] = array(
            'button' => $button,
            'link' => $link,
        );
    }

    protected function addListFooterButton(BaseHTMLItem $button, $link = null)
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
    }

    protected function checkListFetcher()
    {
        if (!($this->listFetcher instanceof ListFetcher)) {
            throw new Exception('Invalid listFetcher set or listFetcher not set');
        }
    }

    protected function addListColumn($key, $caption)
    {
        $this->listColumns[] = array(
            'key' => $key,
            'caption' => $caption,
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

    public function getListRowButtons()
    {
        return $this->listRowButtons;
    }

    public function getListFooterButtons()
    {
        return $this->listFooterButtons;
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
}
