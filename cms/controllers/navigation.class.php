<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;
use WebFW\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\CMS\TreeController;
use WebFW\Core\Classes\HTML\Input;
use WebFW\CMS\Classes\EditTab;
use WebFW\Database\TableGateway;
use WebFW\Database\TreeTableGateway;

class Navigation extends TreeController
{
    public function init()
    {
        parent::init();

        $this->listFetcher = new LFNavigation();
        $this->tableGateway = new TGNavigation();
    }

    public function initList()
    {
        parent::initList();

        $this->sort = array(
            'order_id' => 'ASC',
        );

        $this->addListColumn('caption', 'Caption');
        $this->addListColumn('strChildrenCount', 'Children', true);
        $this->addListColumn('strActive', 'Active', true);

        $this->enableListSorting('sortItems', 'order_id', array('parent_node_id'));
    }

    public function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $readonly = new Input('strParentNodeCaption', null, 'text', null, null);
        $readonly->disable();
        $tab->addField($readonly, 'Parent node');
        $tab->addField(new Input('caption', null, 'text', null, 'caption'), 'Caption');
        $tab->addField(new Input('controller', null, 'text', null, 'controller'), 'Controller');
        $tab->addField(new Input('namespace', null, 'text', null, 'namespace'), 'Namespace');
        $tab->addField(new Input('action', null, 'text', null, 'action'), 'Action');
        $tab->addField(new Input('params', null, 'text', null, 'params'), 'Params');
        $tab->addField(new Input('custom_url', null, 'text', null, 'custom_url'), 'Custom URL');
        $tab->addField(new Input('active', null, 'checkbox', null, 'active'), 'Active', null, true);

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strActive'] = static::getBooleanPrint($item['active']);
            $item['strChildrenCount'] = $item->getChildrenNodeCount();
        }
    }

    public function processEdit(TableGateway &$item)
    {
        $parent = $item->getParentNode();

        if ($parent instanceof TreeTableGateway) {
            $item['strParentNodeCaption'] = $parent->getCaption();
        }
    }
}
