<?php

namespace WebFW\CMS\DBLayer;

use \WebFW\CMS\DBLayer\ListFetchers\Navigation as NavigationLF;
use \WebFW\Core\Route;
use \WebFW\Database\TreeTableGateway;

class Navigation extends TreeTableGateway
{
    protected $childrenNodes = null;

    public function __construct()
    {
        $this->setTable('Navigation', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->nodeLevelColumn = 'node_level';
        $this->parentNodeKeyColumns = 'parent_node_id';
        $this->maximumTreeDepth = 3;
        parent::__construct();
    }

    public function getChildrenNodes()
    {
        if ($this->childrenNodes === null) {
            $treeLF = new NavigationLF();
            $this->childrenNodes = $treeLF->getList(
                array('parent_node_id' => $this->parent_node_id),
                array('order_id' => 'ASC'),
                1000
            );
        }

        return $this->childrenNodes;
    }

    public function beforeSave()
    {
        if ($this->parent_node_id === null) {
            $this->node_level = 0;
        } else {
            $parent = $this->getParentNode();
            if ($parent instanceof $this) {
                $this->node_level = $parent->node_level + 1;
            }
        }
    }

    public function getURL()
    {
        if ($this->custom_url !== null) {
            return $this->custom_url;
        } else {
            $params = array();
            foreach (explode('&', $this->params) as $paramSet) {
                $paramSet = explode('=', $paramSet);
                $params[$paramSet[0]] = $paramSet[1];
            }

            return new Route($this->controller, $this->action, $this->namespace, $params);
        }
    }
}
