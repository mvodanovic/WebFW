<?php

namespace WebFW\CMS\Components;

use \WebFW\Core\Component;
use \WebFW\CMS\Classes\LoggedUser;
use \WebFW\Menu\Menu;
use \WebFW\Menu\Item;
use \WebFW\Menu\BaseNode;
use \WebFW\Core\Route;

class Navigation extends Component
{
    const MAX_DEPTH = 5;

    public function execute()
    {
        $depth = $this->getParam('depth');
        if (!LoggedUser::isLoggedIn() || $depth > static::MAX_DEPTH) {
            $this->useTemplate = false;
            return;
        }

        $menu = $this->getParam('menu');
        if ($depth === 0) {
            $menu = $this->getMenu();
        }

        if ($menu === null || $menu->getChildrenCount() === 0) {
            $this->useTemplate = false;
            return;
        }

        $this->setTplVar('menu', $menu);
        $this->setTplVar('depth', $depth);
        $this->setTplVar('classes', $this->getParam('classes'));
        $this->setTplVar('ownerObject', $this->ownerObject);
    }

    protected function getMenu()
    {
        $menu = new Menu('cms');
        for ($i = 1; $i <= 5; $i++) {
            $item = new Item('Item ' . $i);
            $item->setHref('javascript:select_nav_element(\'item' . $i . '\')');
            $item->setHrefIsFull(true);
            for ($j = 1; $j <= 5; $j++) {
                $subitem = new Item('Subitem ' . $i . '-' . $j);
                if ($i === 1 && $j === 1) {
                    $subitem->setRoute(new Route('CMSTest'));
                    $item->addChild('application_controllers_cmstest', $subitem);
                } else {
                    $subitem->setHref('javascript:select_nav_element(\'item' . $i . '_' . $j . '\')');
                    $subitem->setHrefIsFull(true);
                    $item->addChild('item' . $i . '_' . $j, $subitem);
                }
            }
            $menu->addChild('item' . $i, $item);
        }

        $subitem = new Item('Users');
        $subitem->setRoute(new Route('User', null, '\\Application\\Controllers\\CMS\\'));
        $subitem2 = new Item('Tasks');
        $subitem2->setRoute(new Route('Task', null, '\\Application\\Controllers\\CMS\\'));
        $item = new Item('App');
        $item->setHref('javascript:select_nav_element(\'app\')');
        $item->setHrefIsFull(true);
        $item->addChild('application_controllers_cms_user', $subitem);
        $item->addChild('application_controllers_cms_task', $subitem2);
        $menu->addChild('app', $item);

        $subitem = new Item('CMS Users');
        $subitem->setRoute(new Route('User', null, '\\WebFW\\CMS\\Controllers\\'));
        $subitem2 = new Item('CMS User Types');
        $subitem2->setRoute(new Route('UserType', null, '\\WebFW\\CMS\\Controllers\\'));
        $item = new Item('Root');
        $item->setHref('javascript:select_nav_element(\'root\')');
        $item->setHrefIsFull(true);
        $item->addChild('webfw_cms_controllers_user', $subitem);
        $item->addChild('webfw_cms_controllers_usertype', $subitem2);
        $menu->addChild('root', $item);

        return $menu;
    }

    public function getMenuJSON()
    {
        $items = $this->getMenuJSONRecursive($this->getMenu(), null);
        return json_encode($items, JSON_FORCE_OBJECT);
    }

    protected function getMenuJSONRecursive(BaseNode $menu, $parent)
    {
        $items = array();
        foreach ($menu->getChildren() as $id => $child) {
            $items[$id] = $parent;
            $items += $this->getMenuJSONRecursive($child, $id);
        }

        return $items;
    }

    public function getSelectedMenuItem()
    {
        if ($this->ownerObject === null) {
            return null;
        }

        if (!method_exists($this->ownerObject, 'getSelectedMenuItem')) {
            return null;
        }

        return $this->ownerObject->getSelectedMenuItem();
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'navigation');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
        $this->setParam('depth', 0);
        $this->setParam('menu', null);
        $this->setParam('classes', 'nav_head');
    }
}
