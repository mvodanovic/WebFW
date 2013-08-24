<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\Controller;
use WebFW\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;
use WebFW\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Textarea;
use WebFW\Core\Classes\HTML\Select;
use WebFW\CMS\Classes\EditTab;

class Navigation extends Controller
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
            'node_level' => 'ASC',
            'order_id' => 'ASC',
        );

        $this->addListColumn('node_id', 'Node ID', true);
        $this->addListColumn('parent_node_id', 'Parent node ID', true);
        $this->addListColumn('caption', 'Caption');
        $this->addListColumn('strURL', 'URL');
        $this->addListColumn('order_id', 'Order ID', true);
        $this->addListColumn('strActive', 'Active', true);
    }

    public function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $tab->addField(new Input('parent_node_id', null, 'text', null, 'parent_node_id'), 'Parent node');
        $tab->addField(new Input('order_id', null, 'text', null, 'order_id'), 'Order ID');
        $tab->addField(new Input('caption', null, 'text', null, 'caption'), 'Caption');
        $tab->addField(new Input('controller', null, 'text', null, 'controller'), 'Controller');
        $tab->addField(new Input('namespace', null, 'text', null, 'namespace'), 'Namespace');
        $tab->addField(new Input('action', null, 'text', null, 'action'), 'Action');
        $tab->addField(new Input('params', null, 'text', null, 'params'), 'Params');
        $tab->addField(new Input('custom_url', null, 'text', null, 'custom_url'), 'Custom URL');
        $tab->addField(new Input('active', null, 'checkbox', null, 'active'), 'Active', null, true);

        $this->editTabs[] = $tab;
    }

    protected function initListFilters()
    {
        $this->addListFilter(new Input('f_parent_node_id', null, null, 'input_small', 'parent_node_id'), 'Parent node');
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strURL'] = $item->getURL();
            $item['strActive'] = static::getBooleanPrint($item['active']);
        }
    }
}
