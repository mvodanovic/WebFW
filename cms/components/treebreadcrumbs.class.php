<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\TreeController;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Component;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Database\TreeTableGateway;

class TreeBreadcrumbs extends Component
{
    public function execute()
    {
        if (!($this->ownerObject instanceof TreeController)) {
            $this->useTemplate = false;
            return;
        }

        $node = $this->ownerObject->getTableGateway();
        $nodeColumns = $node->getParentNodeKeyColumns();
        $treeFilter = $this->ownerObject->getParentNodeValues();
        $parentNodeKey = array();
        foreach ($treeFilter as $parentColumn => $value) {
            $parentNodeKey[$nodeColumns[$parentColumn]] = $value;
        }
        try {
            $node->loadBy($parentNodeKey);
        } catch (NotFoundException $e) {
            $node = null;
        }

        $breadcrumbs = array();
        while ($node instanceof TreeTableGateway) {
            $key = array();
            foreach ($node->getParentNodeKeyColumns() as $parentColumn => $childColumn) {
                $key[EditTab::FIELD_PREFIX . $parentColumn] = $node->$childColumn;
            }
            $url = $this->ownerObject->getURL(null, false, $key, false);
            $link = new Link($node->getCaption(), $url);
            $breadcrumbs[] = $link;

            $node = $node->getParentNode();
        }
        $link = new Link('Home', $this->ownerObject->getURL(null, false, null, false));
        $breadcrumbs[] = $link;
        $breadcrumbs[0]->addClass('active');
        $breadcrumbs = array_reverse($breadcrumbs);

        $this->setTplVar('breadcrumbs', $breadcrumbs);
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'treebreadcrumbs');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }
}
