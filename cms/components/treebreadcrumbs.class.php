<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\CMS\TreeController;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Component;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Database\TreeTableGateway;

class TreeBreadcrumbs extends Component
{
    /** @var TreeController */
    protected $controller;

    public function execute()
    {
        if (!($this->controller instanceof TreeController)) {
            $this->useTemplate = false;
            return;
        }

        /** @var TGNavigation $node */
        $node = $this->controller->getTableGateway();
        $nodeColumns = $node->getParentNodeKeyColumns();
        $treeFilter = $this->controller->getParentNodeValues();
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
            $url = $this->controller->getURL(null, false, $key, false);
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
        $link = new Link(null, $this->controller->getURL(null, false, null, false), $options);
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
        $this->setParam('templateDirectory', \WebFW\Core\FW_PATH . '/cms/templates/components/');
    }
}
