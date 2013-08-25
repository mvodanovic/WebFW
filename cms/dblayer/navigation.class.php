<?php

namespace WebFW\CMS\DBLayer;

use WebFW\CMS\DBLayer\ListFetchers\Navigation as NavigationLF;
use WebFW\Core\Route;
use WebFW\Database\TreeTableGateway;

class Navigation extends TreeTableGateway
{
    protected $childrenNodes = null;
    protected $childrenNodeCount = null;

    public function __construct()
    {
        $this->setTable('Navigation', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->nodeLevelColumn = 'node_level';
        $this->parentNodeKeyColumns = array('parent_node_id' => 'node_id');
        $this->maximumTreeDepth = 3;
        parent::__construct();
    }

    public function getChildrenNodes($forceReload = false)
    {
        if ($forceReload === true || $this->childrenNodes === null) {
            $treeLF = new NavigationLF();
            $this->childrenNodes = $treeLF->getList(
                array('parent_node_id' => $this->parent_node_id),
                array('order_id' => 'ASC'),
                1000
            );
        }

        return $this->childrenNodes;
    }

    public function getChildrenNodeCount($forceReload = false)
    {
        if ($forceReload === true || $this->childrenNodeCount === null) {
            $treeLF = new NavigationLF();
            $this->childrenNodeCount = $treeLF->getCount(
                array('parent_node_id' => $this->node_id)
            );
        }

        return $this->childrenNodeCount;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function beforeSave()
    {
        if ($this->parent_node_id === null) {
            $this->node_level = 0;
        } else {
            $parent = $this->getParentNode();
            if ($parent instanceof static) {
                $this->node_level = $parent->node_level + 1;
            }
        }
    }

    public function getURL()
    {
        if ($this->custom_url !== null) {
            return $this->custom_url;
        } elseif ($this->controller !== null) {
            $params = array();
            if ($this->params !== null) {
                foreach (explode('&', $this->params) as $paramSet) {
                    $paramSet = explode('=', $paramSet);
                    if (array_key_exists(1, $paramSet)) {
                        $params[$paramSet[0]] = $paramSet[1];
                    }
                }
            }
            $route = new Route($this->controller, $this->action, $this->namespace, $params);
            return $route->getURL();
        } else {
            return null;
        }
    }
}
