<?php

namespace WebFW\Framework\CMS\Controllers;

use WebFW\Framework\CMS\Classes\ListHelper;
use WebFW\Framework\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;
use WebFW\Framework\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\Framework\CMS\TreeController;
use WebFW\Framework\Core\Classes\ClassHelper;
use WebFW\Framework\Core\Classes\HTML\Input;
use WebFW\Framework\CMS\Classes\EditTab;
use WebFW\Framework\Core\Classes\HTML\Select;
use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Database\TreeTableGateway;
use WebFW\Framework\CMS\Controller;

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

        $controllers = ClassHelper::getClasses(Controller::className(), false);

        $tab = new EditTab('default');

        $readonly = new Input('strParentNodeCaption', 'text');
        $readonly->disable();
        $tab->addField(
            $readonly,
            'Parent node',
            'Parent node caption.'
        );
        $tab->addField(
            new Input('caption', 'text'),
            'Caption',
            'Caption displayed where needed.'
        );
        $tab->addField(
            new Select('controller', ListHelper::toKeyValueList($controllers, true), true),
            'Controller',
            "Controller name.\nLeave blank to use the node as a parent node."
        );
        $tab->addField(
            new Input('action', 'text'),
            'Action',
            'Can be left blank for default action.'
        );
        $tab->addField(
            new Input('params', 'text'),
            'URL Parameters',
            "URL GET syntax.\nBlank for no additional parameters."
        );
        $tab->addField(
            new Input('custom_url', 'url'),
            'Custom URL',
            "Overrides routing parameters.\nCan be used to link to anything, including external links."
        );
        $tab->addField(
            new Input('active', 'checkbox'),
            'Active',
            'If inactive, won\'t be visible as well as all it\'s children.',
            true
        );

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            /** @var TGNavigation $item */
            $item['strActive'] = static::getBooleanPrint($item['active']);
            $item['strChildrenCount'] = $item->getChildrenNodeCount();
        }
    }

    public function processEdit(TableGateway &$item)
    {
        /** @var TreeTableGateway $item */
        $parent = $item->getParentNode();

        if ($parent instanceof TreeTableGateway) {
            $item['strParentNodeCaption'] = $parent->getCaption();
        }
    }
}
