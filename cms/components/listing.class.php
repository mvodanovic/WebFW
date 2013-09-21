<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\ListRowAction;
use WebFW\Core\ArrayAccess;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Component;
use WebFW\Core\Exception;
use WebFW\CMS\Controller;

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
        $controllerName = $this->ownerObject->getName();
        $namespace = $this->ownerObject->getNamespace();
        $paginatorFilter = $this->ownerObject->getPaginatorFilter();
        $messages = $this->ownerObject->getMessages();
        $listActions = $this->ownerObject->getListActions();
        $listRowActions = $this->ownerObject->getListRowActions();
        $listMassActions = $this->ownerObject->getListMassActions();
        $hasCheckboxes = empty($listMassActions) ? false : true; //$this->ownerObject->getListHasCheckboxes();

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
        $this->setTplVar('messages', $messages);
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
        $handlerFunction = $action->getHandlerFunction();

        if ($handlerFunction !== null) {
            $params = $this->ownerObject->$handlerFunction($listRow);
        } else if ($listRow !== null) {
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

        if ($params !== null) {
            return $action->getLink($params)->parse();
        } else {
            return null;
        }
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

    public function getRowMetadata(&$listRow)
    {
        $metadata = '';

        if ($this->ownerObject->isSortingEnabled()) {
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
            $params = json_encode($params, JSON_FORCE_OBJECT);
            $metadata .= ' data-key="' . htmlspecialchars($params) . '"';

            $sortingDef = $this->ownerObject->getSortingDef();
            if (!empty($sortingDef['groupColumns'])) {
                $group = array();
                foreach ($sortingDef['groupColumns'] as $column) {
                    $group[$column] = $listRow[$column];
                }
                $group = json_encode($group, JSON_FORCE_OBJECT);
                $metadata .= ' data-group="' . htmlspecialchars($group) . '"';
            }
        }

        return $metadata;
    }
}
