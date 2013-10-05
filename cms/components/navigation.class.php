<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\PermissionsHelper as PH;
use WebFW\CMS\CMSLogin;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Core\Component;
use WebFw\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\CMS\DBLayer\ListFetchers\Navigation as LFNavigation;
use \WebFW\CMS\DBLayer\Navigation as Node;

class Navigation extends Component
{
    protected $navList = array();

    public function execute()
    {
        if ($this->ownerObject instanceof CMSLogin) {
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
