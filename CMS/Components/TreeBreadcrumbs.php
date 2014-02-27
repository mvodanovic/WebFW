<?php

namespace mvodanovic\WebFW\CMS\Components;

use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\CMS\DBLayer\Navigation as TGNavigation;
use mvodanovic\WebFW\CMS\TreeController;
use mvodanovic\WebFW\Core\Classes\HTML\Link;
use mvodanovic\WebFW\Core\Component;
use mvodanovic\WebFW\Core\Controller;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Database\TreeTableGateway;

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
        $this->setParam('templateDirectory', \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates/Components');
    }
}
