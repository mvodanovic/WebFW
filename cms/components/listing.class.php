<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\ListAction;
use WebFW\CMS\Classes\ListRowAction;
use \WebFW\Core\ArrayAccess;
use WebFW\Core\Classes\HTML\Input;
use \WebFW\Core\Component;
use \WebFW\Core\Route;
use \WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use \WebFW\Core\Classes\HTML\Link;
use \WebFW\Core\Exception;
use \WebFW\CMS\Controller;

class Listing extends Component
{
    public function execute()
    {
        if (!($this->ownerObject instanceof Controller)) {
            throw new Exception('Owner must be an instance of \\WebFW\\CMS\\Controller');
        }
        $listFetcher = $this->ownerObject->getListFetcher();
        $filter = $this->ownerObject->getFilter();
        $sort = $this->ownerObject->getSort();
        $page = $this->ownerObject->getPage();
        $itemsPerPage = $this->ownerObject->getItemsPerPage();
        $listColumns = $this->ownerObject->getListColumns();
        $columnCount = count($listColumns);
        $controllerName = $this->ownerObject->getControllerName();
        $namespace = $this->ownerObject->getNamespace();
        $paginatorFilter = $this->ownerObject->getPaginatorFilter();
        $errorMessage = $this->ownerObject->getErrorMessage();
        //$headerButtons = $this->ownerObject->getListHeaderButtons();
        //$rowButtons = $this->ownerObject->getListRowButtons();
        //$footerButtons = $this->ownerObject->getListFooterButtons();
        $listActions = $this->ownerObject->getListActions();
        $listRowActions = $this->ownerObject->getListRowActions();
        $listMassActions = $this->ownerObject->getListMassActions();
        $hasCheckboxes = $this->ownerObject->getListHasCheckboxes();

//        foreach ($headerButtons as &$buttonDef) {
//            $link = $buttonDef['link'];
//            if ($link instanceof Route) {
//                $link = $link->getURL();
//            }
//            $button = $buttonDef['button'];
//            if ($button instanceof Link) {
//                $button->addCustomAttribute('href', $link);
//            }
//            $buttonDef = $button->parse();
//        }

//        foreach ($footerButtons as &$buttonDef) {
//            $link = $buttonDef['link'];
//            if ($link instanceof Route) {
//                $link = $link->getURL();
//            }
//            $button = $buttonDef['button'];
//            if ($button instanceof Link) {
//                $button->addCustomAttribute('href', $link);
//            }
//            $buttonDef = $button->parse();
//        }

        if (!empty($listRowActions)) {
            $columnCount++;
        }

        if ($hasCheckboxes === true) {
            $columnCount++;
        }

        $listData = $listFetcher->getList($filter, $sort, $itemsPerPage, ($page - 1) * $itemsPerPage);
        $this->ownerObject->processList($listData);
        $totalCount = $listFetcher->getCount($filter);

        $this->setTplVar('listData', $listData);
        $this->setTplVar('listColumns', $listColumns);
        $this->setTplVar('totalCount', $totalCount);
        $this->setTplVar('columnCount', $columnCount);
        $this->setTplVar('page', $page);
        $this->setTplVar('itemsPerPage', $itemsPerPage);
        $this->setTplVar('controllerName', $controllerName);
        $this->setTplVar('namespace', $namespace);
        $this->setTplVar('paginatorFilter', $paginatorFilter);
        $this->setTplVar('errorMessage', $errorMessage);
        $this->setTplVar('listActions', $listActions);
        $this->setTplVar('listRowActions', $listRowActions);
        $this->setTplVar('listMassActions', $listMassActions);
        $this->setTplVar('hasCheckboxes', $hasCheckboxes);
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'listing');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }

    public function getRowButton(ListRowAction &$action, &$listRow)
    {
        $params = array();
        if ($listRow !== null) {
            $primaryKeyColumns = $this->ownerObject->getPrimaryKeyColumns();
            if (is_array($primaryKeyColumns)) {
                foreach ($primaryKeyColumns as $column) {
                    if (!ArrayAccess::keyExists($column, $listRow)) {
                        $params = array();
                        break;
                    }
                    $params['pk_' . $column] = $listRow[$column];
                }
            }
        }

        return $action->getLink($params)->parse();
    }

    public function getRowCheckbox(&$listRow)
    {
        $params = array();
        if ($listRow !== null) {
            $primaryKeyColumns = $this->ownerObject->getPrimaryKeyColumns();
            if (is_array($primaryKeyColumns)) {
                foreach ($primaryKeyColumns as $column) {
                    if (!ArrayAccess::keyExists($column, $listRow)) {
                        $params = array();
                        break;
                    }
                    $params[$column] = $listRow[$column];
                }
            }
        }

        $checkbox = new Input(null, null, 'checkbox', 'row_selector');
        foreach ($params as $key => $value) {
            $checkbox->addCustomAttribute('data-' . $key, $value);
        }

        return $checkbox->parse();
    }
}
