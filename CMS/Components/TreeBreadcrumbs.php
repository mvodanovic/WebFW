<?php

namespace WebFW\Framework\CMS\Components;

use WebFW\Framework\CMS\Classes\EditTab;
use WebFW\Framework\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\Framework\CMS\TreeController;
use WebFW\Framework\Core\Classes\HTML\Link;
use WebFW\Framework\Core\Component;
use WebFW\Framework\Core\Controller;
use WebFW\Framework\Core\Exceptions\NotFoundException;
use WebFW\Framework\Database\TreeTableGateway;

class TreeBreadcrumbs extends Component
{
    public function execute()
    {
        $controller = Controller::getInstance();
        if (!($controller instanceof TreeController)) {
            $this->useTemplate = false;
            return;
        }

        /** @var TGNavigation $node */
        $node = $controller->getTableGateway();
        $nodeColumns = $node->getParentNodeKeyColumns();
        $treeFilter = $controller->getParentNodeValues();
        $parentNodeKey = array();
        foreach ($treeFilter as $parentColumn => $value) {
            $parentNodeKey[$nodeColumns[$parentColumn]] = $value;
        }
        try {
            $node->loadBy($parentNodeKey);
        } catch (NotFoundException $e) {
            $node = null;
        }

        /** @var Link[] $breadcrumbs */
        $breadcrumbs = array();
        while ($node instanceof TreeTableGateway) {
            $key = array();
            foreach ($node->getParentNodeKeyColumns() as $parentColumn => $childColumn) {
                $key[EditTab::FIELD_PREFIX . $parentColumn] = $node->$childColumn;
            }
            $url = $controller->getURL(null, false, $key, false);
            $options = array(
                'label' => $node->getCaption(),
            );
            $link = new Link($node->getCaption(), $url, $options);
            $breadcrumbs[] = $link;

            $node = $node->getParentNode();
        }
        $options = array(
            'icons' => array('primary' => 'ui-icon-home'),
            'text' => false,
        );
        $link = new Link(null, $controller->getURL(null, false, null, false), $options);
        $breadcrumbs[] = $link;
        $breadcrumbs[0]->addClass('ui-state-active');
        $breadcrumbs[0]->addClass('ui-state-persist');
        $breadcrumbs = array_reverse($breadcrumbs);

        $this->setTplVar('breadcrumbs', $breadcrumbs);
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'treebreadcrumbs');
        $this->setParam('templateDirectory', \WebFW\Framework\Core\FW_PATH . '/CMS/Templates/Components');
    }
}
