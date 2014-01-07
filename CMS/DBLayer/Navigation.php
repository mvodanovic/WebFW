<?php

namespace WebFW\Framework\CMS\DBLayer;

use WebFW\Framework\CMS\DBLayer\ListFetchers\Navigation as NavigationLF;
use WebFW\Framework\CMS\DBLayer\Tables\Navigation as NavigationT;
use WebFW\Framework\Core\Classes\HTML\Link;
use WebFW\Framework\Core\Route;
use WebFW\Framework\Database\TreeTableGateway;

class Navigation extends TreeTableGateway
{
    protected $childrenNodes = null;
    protected $childrenNodeCount = null;

    public function __construct()
    {
        $this->setTable(NavigationT::getInstance());
        $this->nodeLevelColumn = 'node_level';
        $this->parentNodeKeyColumns = array('parent_node_id' => 'node_id');
        $this->maximumTreeDepth = 3;
        parent::__construct();
    }

    public function getChildrenNodes($forceReload = false, $onlyActive = false)
    {
        if ($forceReload === true || $this->childrenNodes === null) {
            $filter = array('parent_node_id' => $this->node_id);
            if ($onlyActive === true) {
                $filter['active'] = true;
            }
            $sort = array('order_id' => 'ASC');
            $treeLF = new NavigationLF();
            $this->childrenNodes = $treeLF->getList($filter, $sort, 1000);
        }

        return $this->childrenNodes;
    }

    public function getChildrenNodeCount($forceReload = false, $onlyActive = false)
    {
        if ($forceReload === true || $this->childrenNodeCount === null) {
            $filter = array('parent_node_id' => $this->node_id);
            if ($onlyActive === true) {
                $filter['active'] = true;
            }
            $treeLF = new NavigationLF();
            $this->childrenNodeCount = $treeLF->getCount($filter);
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

    public function beforeSaveNew()
    {
        $listFetcher = new NavigationLF();
        $list = $listFetcher->getList(array('parent_node_id' => $this->parent_node_id), array('order_id' => 'DESC'), 1);
        if (empty($list)) {
            $this->order_id = 0;
        } else {
            $this->order_id = $list[0]['order_id'] + 1;
        }
    }

    public function validateData()
    {
        /// TODO: move to automatic foreign key check?
        if ($this->parent_node_id !== null) {
            if ($this->getParentNode() === null) {
                $this->addValidationError('parent_node_id', 'Parent node doesn\'t exist');
            }
        }

        if ($this->controller === null) {
            if ($this->action !== null) {
                $this->addValidationError('action', 'An action needs a controller defined');
            }

            if ($this->params !== null) {
                $this->addValidationError('params', 'URL parameters need both controller and namespace defined');
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
            $route = new Route($this->controller, $this->action, null, $params);
            return $route->getURL(false);
        } else {
            return 'javascript:CMSPage.selectNavElement(' . $this->node_id . ')';
        }
    }

    public function getLink()
    {
        $options = array(
            'label' => $this->getCaption(),
        );
        if ($this->custom_url !== null) {
            $options['icons'] = array('primary' => 'ui-icon-extlink');
        } elseif ($this->controller === null) {
            $options['icons'] = array('secondary' => 'ui-icon-triangle-1-s');
        }
        $link = new Link(null, $this->getURL(), $options);
        if ($this->custom_url !== null) {
            $link->setAttribute('target', '_blank');
        }
        return $link->parse();
    }

    public function getTreeIDList()
    {
        $parent = $this->getParentNode();
        if ($parent instanceof static) {
            $list = $parent->getTreeIDList();
        } else {
            $list = array();
        }

        $list[] = $this->node_id;

        return $list;
    }
}
