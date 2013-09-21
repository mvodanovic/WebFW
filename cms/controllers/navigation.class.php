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
    protected function init()
    {
        $this->pageTitle = 'CMS Navigation';

        parent::init();

        $this->listFetcher = new LFNavigation();
        $this->tableGateway = new TGNavigation();
    }

    protected function initList()
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

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $readonly = new Input('strParentNodeCaption', null, 'text', null, null);
        $readonly->disable();
        $tab->addField(
            $readonly,
            'Parent node',
            'Parent node caption.'
        );
        $tab->addField(
            new Input('caption', null, 'text', null, 'caption'),
            'Caption',
            'Caption displayed where needed.'
        );
        $tab->addField(
            new Input('controller', null, 'text', null, 'controller'),
            'Controller',
            "Controller name without the namespace.\nLeave all route fields blank to use the node as a parent node."
        );
        $tab->addField(
            new Input('namespace', null, 'text', null, 'namespace'),
            'Namespace',
            'Full namespace including both the starting and ending slash.'
        );
        $tab->addField(
            new Input('action', null, 'text', null, 'action'),
            'Action',
            'Can be left blank for default action.'
        );
        $tab->addField(
            new Input('params', null, 'text', null, 'params'),
            'URL Parameters',
            "URL GET syntax.\nBlank for no additional parameters."
        );
        $tab->addField(
            new Input('custom_url', null, 'url', null, 'custom_url'),
            'Custom URL',
            "Overrides routing parameters.\nCan be used to link to anything, including external links."
        );
        $tab->addField(
            new Input('active', null, 'checkbox', null, 'active'),
            'Active',
            'If inactive, won\'t be visible as well as all it\'s children.',
            true
        );

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
