<?php

namespace mvodanovic\WebFW\CMS\Components;

use mvodanovic\WebFW\CMS\Classes\ListRowAction;
use mvodanovic\WebFW\Core\ArrayAccess;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Component;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\CMS\ListController;
use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Database\TableColumns\Column;
use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\Dev\Controller;

class Listing extends Component
{
    /** @var ListController */
    protected $controller;

    /**
     * @throws \mvodanovic\WebFW\Core\Exception
     */
    public function execute()
    {
        $this->controller = Controller::getInstance();

        if (!($this->controller instanceof ListController)) {
            throw new Exception('Owner must be an instance of ' . ListController::className());
        }
        /** @var ListFetcher $listFetcher */
        $listFetcher = $this->controller->getListFetcher();
        $filter = $this->controller->getFilter();
        $sort = $this->controller->getSort();
        $page = $this->controller->getPage();
        $itemsPerPage = $this->controller->getItemsPerPage();
        $listColumns = $this->controller->getListColumns();
        $columnCount = count($listColumns);
        $controllerName = $this->controller->className();
        $filterValues = $this->controller->getFilterValues(true);
        $messages = $this->controller->getMessages();
        $listActions = $this->controller->getListActions();
        $listRowActions = $this->controller->getListRowActions();
        $listMassActions = $this->controller->getListMassActions();
        $hasCheckboxes = empty($listMassActions) ? false : true;

        if (!empty($listRowActions)) {
            $columnCount++;
        }

        if ($hasCheckboxes === true) {
            $columnCount++;
        }

        $listData = $listFetcher->getList($filter, $sort, $itemsPerPage, ($page - 1) * $itemsPerPage);
        $this->controller->processList($listData);
        $totalCount = $listFetcher->getCount($filter);

        $this->setTplVar('listData', $listData);
        $this->setTplVar('listColumns', $listColumns);
        $this->setTplVar('totalCount', $totalCount);
        $this->setTplVar('columnCount', $columnCount);
        $this->setTplVar('page', $page);
        $this->setTplVar('itemsPerPage', $itemsPerPage);
        $this->setTplVar('controllerName', $controllerName);
        $this->setTplVar('filterValues', $filterValues);
        $this->setTplVar('messages', $messages);
        $this->setTplVar('listActions', $listActions);
        $this->setTplVar('listRowActions', $listRowActions);
        $this->setTplVar('listMassActions', $listMassActions);
        $this->setTplVar('hasCheckboxes', $hasCheckboxes);
        $this->setTplVar('sortingDefinitionJSON', $this->controller->getJSONSortingDef());
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'listing');
        $this->setParam('templateDirectory', \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates/Components');
    }

    public function getRowButton(ListRowAction $action, TableGateway $listRow)
    {
        $handlerFunction = $action->getHandlerFunction();

        if ($handlerFunction !== null) {
            $params = $this->controller->$handlerFunction($listRow);
            if (is_array($params)) {
                return $action->getLink(null, $params)->parse();
            } else {
                return null;
            }
        } else if ($listRow !== null) {
            return $action->getLink($listRow)->parse();
        } else {
            return null;
        }
    }

    public function getRowCheckbox(&$listRow)
    {
        $params = array();
        if ($listRow !== null) {
            $primaryKeyColumns = $this->controller->getPrimaryKeyColumns();
            if (is_array($primaryKeyColumns)) {
                foreach ($primaryKeyColumns as $column) {
                    /** @var Column $column */
                    if (!ArrayAccess::keyExists($column->getName(), $listRow)) {
                        $params = array();
                        break;
                    }
                    $params[$column->getName()] = $listRow[$column->getName()];
                }
            }
        }

        $checkbox = new Input(null, Input::INPUT_CHECKBOX);
        $checkbox->addClass('row_selector');
        foreach ($params as $key => $value) {
            $checkbox->setAttribute('data-' . $key, $value);
        }

        return $checkbox->parse();
    }

    public function getRowMetadata(&$listRow)
    {
        $metadata = '';

        if ($this->controller->isSortingEnabled()) {
            $params = array();
            if ($listRow !== null) {
                $primaryKeyColumns = $this->controller->getPrimaryKeyColumns();
                if (is_array($primaryKeyColumns)) {
                    foreach ($primaryKeyColumns as $column) {
                        /** @var Column $column */
                        if (!ArrayAccess::keyExists($column->getName(), $listRow)) {
                            $params = array();
                            break;
                        }
                        $params[$column->getName()] = $listRow[$column->getName()];
                    }
                }
            }
            $params = json_encode($params, JSON_FORCE_OBJECT);
            $metadata .= ' data-key="' . htmlspecialchars($params) . '"';

            $sortingDef = $this->controller->getSortingDef();
            if (!empty($sortingDef['groupColumns'])) {
                $group = array();
                foreach ($sortingDef['groupColumns'] as $column) {
                    /** @var string $column */
                    $group[$column] = $listRow[$column];
                }
                $group = json_encode($group, JSON_FORCE_OBJECT);
                $metadata .= ' data-group="' . htmlspecialchars($group) . '"';
            }
        }

        return $metadata;
    }
}
