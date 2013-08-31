<?php

namespace WebFW\CMS\Components;

use WebFW\Core\Component;
use WebFW\CMS\Classes\LoggedUser;
use WebFw\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;

class Navigation extends Component
{
    protected $navList = array();

    public function execute()
    {
        $lfNavigation = new LFNavigation();

        $filter = array(
            'parent_node_id' => null,
            'node_level' => 0,
            'active' => true,
        );
        $sort = array(
            'order_id' => 'ASC',
        );
        $this->navList[] = $nodesToProcess = $lfNavigation->getList($filter, $sort, 1000);

        while (!empty($nodesToProcess)) {
            $node = array_shift($nodesToProcess);

            $children = $node->getChildrenNodes(false, true);
            $this->navList[] = $children;
            $nodesToProcess = array_merge($nodesToProcess, $children);
        }

        $this->setTplVar('navList', $this->navList);
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

    public function getNodeName(TGNavigation &$node)
    {
        $name = strtolower(str_replace('\\', '_', $node->namespace . $node->controller));
        if (strpos($name, '_') === 0) {
            $name = substr($name, 1);
        }
        return $name;
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'navigation');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }
}
