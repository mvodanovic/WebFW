<?php

namespace WebFW\CMS;

use WebFW\CMS\Classes\EditAction;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\Classes\ListAction;
use WebFW\CMS\Classes\ListRowAction;
use WebFW\CMS\Classes\PermissionsHelper;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Exception;
use WebFW\Core\Request;
use WebFW\Database\TreeTableGateway;

abstract class TreeController extends ListController
{
    protected $treeFilter = null;

    protected function afterInit()
    {
        parent::afterInit();

        $this->treeFilter = $this->getParentNodeValues(false);
    }

    protected function afterInitList()
    {
        parent::afterInitList();

        $this->filter += $this->getParentNodeValues();
    }

    protected function afterInitEdit()
    {
        parent::afterInitEdit();

        foreach ($this->tableGateway->getParentNodeKeyColumns() as $parentColumn => $childColumn)
        {
            $firstEditTab = reset($this->editTabs);

            $fieldAlreadyDefined = false;
            foreach ($this->editTabs as $editTab) {
                if ($editTab->hasField($parentColumn)) {
                    $fieldAlreadyDefined = true;
                    break;
                }
            }

            if (!$fieldAlreadyDefined) {
                $firstEditTab->addField(new Input($parentColumn, null, 'hidden'), null);
            }
        }
    }

    protected function checkTableGateway()
    {
        if (!($this->tableGateway instanceof TreeTableGateway)) {
            throw new Exception('Invalid treeTableGateway set or treeTableGateway not set');
        }
    }

    protected function initListActions()
    {
        $buttonFilter = array();
        foreach ($this->treeFilter as $key => $value) {
            $buttonFilter[EditTab::FIELD_PREFIX . $key] = $value;
        }

        /// New
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-plusthick'),
                'label' => 'Add item',
            );
            $HTMLItem = new Link(null, $this->getURL('editItem', false, $buttonFilter, false), $options);
            $listAction = new ListAction($HTMLItem);
            $this->registerListAction($listAction);
        }
    }

    protected function initListRowActions()
    {
        /// Children
        $options = array(
            'icons' => array('primary' => 'ui-icon-folder-open'),
            'text' => false,
        );
        $link = new Link(null, null, $options);
        $route = $this->getRoute(null, false);
        $listRowAction = new ListRowAction($link, $route);
        $listRowAction->setHandlerFunction('listRowHandlerChildren');
        $this->registerListRowAction($listRowAction);

        /// Delete
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-trash'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $link->addEvent('click', 'confirmAction', array('message' => "Item will be deleted.\nAre you sure?"));
            $route = $this->getRoute('deleteItem');
            $listRowAction = new ListRowAction($link, $route);
            $listRowAction->setHandlerFunction('listRowHandlerDelete');
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

        /// Add
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-plusthick'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $route = $this->getRoute('editItem');
            $listRowAction = new ListRowAction($link, $route);
            $listRowAction->setHandlerFunction('listRowHandlerAdd');
            $this->registerListRowAction($listRowAction);
        }
    }

    protected function initEditActions()
    {
        $primaryKeyValues = $this->getPrimaryKeyValues();

        /// Save
        if (empty($primaryKeyValues) && PermissionsHelper::checkForController($this, UTCP::TYPE_INSERT)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-disk'),
                'label' => 'Save new',
            );
            $HTMLItem = new Button(null, 'submit', $options);
            $editAction = new EditAction($HTMLItem);
            $this->registerEditAction($editAction);
        } elseif (PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-disk'),
                'label' => 'Update',
            );
            $HTMLItem = new Button(null, 'submit', $options);
            $editAction = new EditAction($HTMLItem);
            $this->registerEditAction($editAction);
        }

        /// Cancel
        $options = array(
            'icons' => array('primary' => 'ui-icon-cancel'),
            'label' => 'Cancel',
        );
        $HTMLItem = new Link(null, $this->getURL(null, false, $this->treeFilter, false), $options);
        $editAction = new EditAction($HTMLItem);
        $this->registerEditAction($editAction);

        /// Delete
        if (!empty($primaryKeyValues) && PermissionsHelper::checkForController($this, UTCP::TYPE_DELETE)) {
            if ($this->tableGateway->getChildrenNodeCount() === 0) {
                $options = array(
                    'icons' => array('primary' => 'ui-icon-trash'),
                    'label' => 'Delete',
                );
                $HTMLItem = new Link(null, $this->getURL('deleteItem', true, null, false), $options);
                $HTMLItem->addEvent(
                    'click',
                    'confirmAction',
                    array('message' => "Item will be deleted.\nAre you sure?")
                );
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

    public function getEditRequestValues()
    {
        $values = parent::getEditRequestValues();
        $values = array_merge($values, $this->treeFilter);
        return $values;
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
