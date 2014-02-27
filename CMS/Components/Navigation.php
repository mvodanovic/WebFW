<?php

namespace mvodanovic\WebFW\CMS\Components;

use mvodanovic\WebFW\CMS\Classes\PermissionsHelper as PH;
use mvodanovic\WebFW\CMS\CMSLogin;
use mvodanovic\WebFW\CMS\Classes\LoggedUser;
use mvodanovic\WebFW\CMS\Controller;
use mvodanovic\WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use mvodanovic\WebFW\Core\Component;
use mvodanovic\WebFW\CMS\DBLayer\Navigation as TGNavigation;
use mvodanovic\WebFW\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;
use \mvodanovic\WebFW\CMS\DBLayer\Navigation as Node;

class Navigation extends Component
{
    /** @var Controller */
    protected $controller;

    protected $navList = array();

    public function execute()
    {
        $this->controller = Controller::getInstance();

        if ($this->controller instanceof CMSLogin) {
            $this->useTemplate = false;
            return;
        }

        $this->navList = $this->buildNavList();

        $this->setTplVar('navList', $this->navList);
    }

    protected function buildNavList(Node $node = null)
    {
        $navList = array();

        /// Top level, root nodes are fetched from the database
        if ($node === null) {
            $lfNavigation = new LFNavigation();
            $filter = array(
                'parent_node_id' => null,
                'node_level' => 0,
                'active' => true,
            );
            $sort = array(
                'order_id' => 'ASC',
            );
            $nodesToProcess = $lfNavigation->getList($filter, $sort, 1000);
        }

        /// Lower levels, children nodes are fetched from the current one
        else {
            $nodesToProcess = $node->getChildrenNodes(false, true);
        }

        /// List of nodes in the current level which is to be prepended to the tree
        $currentNavList = array();

        foreach ($nodesToProcess as $childNode) {
            /// Only for non-root users...
            if (!LoggedUser::isRoot()) {
                /// Check permissions, but only for controller-defined nodes
                if ($childNode->controller !== null &&
                    !PH::checkForControllerByName($childNode->controller, $childNode->namespace, UTCP::TYPE_SELECT)) {
                    continue;
                }
            }

            /// Recursion
            $childNavList = $this->buildNavList($childNode);

            /// Skip nodes which don't have any visible children and have no controller or custom URL defined
            if (empty($childNavList) && $childNode->controller === null && $childNode->custom_url === null) {
                continue;
            }

            /// Add the current node to the current list and append it's children to navList
            $currentNavList[] = $childNode;
            $navList = array_merge($navList, $childNavList);
        }

        /// Prepend the current list to navList
        if (!empty($currentNavList)) {
            array_unshift($navList, $currentNavList);
        }

        return $navList;
    }

    public function getSelectedMenuItem()
    {
        if ($this->controller === null) {
            return null;
        }

        if (!method_exists($this->controller, 'getSelectedMenuItem')) {
            return null;
        }

        return $this->controller->getSelectedMenuItem();
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
        $this->setParam('templateDirectory', \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates/Components');
    }
}
