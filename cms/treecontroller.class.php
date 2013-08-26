<?php

namespace WebFW\CMS;

use WebFW\CMS\Classes\EditAction;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\Classes\ListAction;
use WebFW\CMS\Classes\ListRowAction;
use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Exception;
use WebFW\Core\Request;
use WebFW\Database\TreeTableGateway;

abstract class TreeController extends Controller
{
    protected $parentNode = null;
    protected $treeFilter = null;

    public function listItems()
    {
        $this->initList();
        $this->checkListFetcher();
        $this->checkTableGateway();

        $this->treeFilter = $this->getParentNodeValues(false);
        $this->filter += $this->getParentNodeValues();

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

        $this->treeFilter = $this->getParentNodeValues(false);

        if (empty($this->editTabs)) {
            $this->editTabs[] = new EditTab('auto');
        }
        foreach ($this->getParentNodeValues() as $column => $value)
        {
            $this->tableGateway->$column = $value;
            reset($this->editTabs)->addField(new Input($column, $value, 'hidden', null, $column), null);
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

        foreach ($this->getParentNodeValues(false) as $column => $value)
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
                        $this->tableGateway->$formItemName = $value;
                        var_dump($formItemName, $value);
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

        if ($this->tableGateway->getChildrenNodeCount() === 0) {
            $this->tableGateway->delete();
        }

        $this->setRedirectUrl($this->getURL(null, true, null, false), true);
    }

    public function massDeleteItems()
    {
        $this->setRedirectUrl($this->getURL(null, true, null, false), true);
    }

    protected function checkTableGateway()
    {
        if (!($this->tableGateway instanceof TreeTableGateway)) {
            throw new Exception('Invalid treeTableGateway set or treeTableGateway not set');
        }
    }

    protected function initListActions()
    {
        /// New
        $HTMLItem = new Link('Add item', $this->getURL('editItem', false, $this->treeFilter), Link::IMAGE_ADD);
        $listAction = new ListAction($HTMLItem);
        $this->registerListAction($listAction);
    }

    protected function initListRowActions()
    {
        /// Children
        $link = new Link(null, null, Link::IMAGE_SEARCH);
        $route = $this->getRoute(null, false);
        $listRowAction = new ListRowAction($link, $route);
        $listRowAction->setHandlerFunction('listRowHandlerChildren');
        $this->registerListRowAction($listRowAction);

        /// Delete
        $link = new Link(null, null, Link::IMAGE_DELETE);
        $link->addCustomAttribute('onclick', "return confirm('Item will be deleted.\\nAre you sure?');");
        $route = $this->getRoute('deleteItem');
        $listRowAction = new ListRowAction($link, $route);
        $listRowAction->setHandlerFunction('listRowHandlerDelete');
        $this->registerListRowAction($listRowAction);

        /// Edit
        $link = new Link(null, null, Link::IMAGE_EDIT);
        $route = $this->getRoute('editItem');
        $listRowAction = new ListRowAction($link, $route);
        $this->registerListRowAction($listRowAction);

        /// Add
        $link = new Link(null, null, Link::IMAGE_ADD);
        $route = $this->getRoute('editItem');
        $listRowAction = new ListRowAction($link, $route);
        $listRowAction->setHandlerFunction('listRowHandlerAdd');
        $this->registerListRowAction($listRowAction);
    }

    protected function initListMassActions() {}

    protected function initEditActions()
    {
        /// Save
        $HTMLItem = new Button(null, 'Save', Link::IMAGE_SAVE, 'submit');
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);

        /// Cancel
        $HTMLItem = new Link('Cancel', $this->getURL(null, false, $this->treeFilter), Link::IMAGE_CANCEL);
        $HTMLItem->addCustomAttribute('onclick', "return confirm('Any unsaved changes will be lost.\\nAre you sure?');");
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);

        /// Delete
        $primaryKeyValues = $this->getPrimaryKeyValues(false);
        if (!empty($primaryKeyValues)) {
            if ($this->tableGateway->getChildrenNodeCount() === 0) {
                $HTMLItem = new Link('Delete', $this->getURL('deleteItem'), Link::IMAGE_DELETE);
                $HTMLItem->addCustomAttribute('onclick', "return confirm('Item will be deleted.\\nAre you sure?');");
                $editAction = new EditAction($HTMLItem);
                $editAction->makeRightAligned();
                $this->registerEditAction($editAction);
            }
        }
    }

    public function getTreeFilter()
    {
        return $this->treeFilter;
    }

    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    public function getParentNodeValues($includeEmptyValues = true, $useChildKeys = false)
    {
        $parentNodeValues = array();
        foreach ($this->tableGateway->getParentNodeKeyColumns() as $parentColumn => $childColumn)
        {
            $key = $useChildKeys === true ? $childColumn : $parentColumn;
            $parentColumn = EditTab::FIELD_PREFIX . $parentColumn;
            $value = Request::getInstance()->$parentColumn;
            if ($value !== null || $includeEmptyValues === true) {
                $parentNodeValues[$key] = $value;
            }
        }

        return $parentNodeValues;
    }

    public function listRowHandlerChildren(TreeTableGateway $item)
    {
        if ($item->getChildrenNodeCount() === 0) {
            return null;
        }

        $params = array();
        foreach ($item->getParentNodeKeyColumns() as $parentColumn => $childColumn) {
            $params[EditTab::FIELD_PREFIX . $parentColumn] = $item->$childColumn;
        }
        return $params;
    }

    public function listRowHandlerDelete(TreeTableGateway $item)
    {
        if ($item->getChildrenNodeCount() > 0) {
            return null;
        }

        return $item->getPrimaryKeyValues();
    }

    public function listRowHandlerAdd(TreeTableGateway $item)
    {
        $params = array();
        foreach ($item->getParentNodeKeyColumns() as $parentColumn => $childColumn) {
            $params[EditTab::FIELD_PREFIX . $parentColumn] = $item->$childColumn;
        }

        return $params;
    }
}
