<?php

namespace WebFW\Core\Components;

use WebFW\Core\Component;
use WebFW\Core\Router;
use WebFW\Core\Controller;

class Paginator extends Component
{
    protected $lastPage;

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $templateDirectory = \WebFW\Core\FW_PATH . DIRECTORY_SEPARATOR
            . 'core' . DIRECTORY_SEPARATOR
            . 'templates' . DIRECTORY_SEPARATOR;

        $this->setParam('templateDirectory', $templateDirectory);
        $this->setParam('template', 'paginator');
        $this->setParam('page', 1);
        $this->setParam('totalItemsCount', 0);
        $this->setParam('itemsPerPage', 30);
        $this->setParam('urlBase', null);
        $this->setParam('displayedPageRange', 3);
        $this->setParam('ctl', null);
        $this->setParam('action', null);
        $this->setParam('ns', null);
        $this->setParam('params', array());
        $this->setParam('pageParamName', 'p');
        $this->setParam('escapeAmps', true);
    }

    public function execute()
    {
        if ($this->getParam('page') < 1) {
            $this->useTemplate = false;
            return;
        }

        if ($this->getParam('totalItemsCount') <= $this->getParam('itemsPerPage')) {
            $this->useTemplate = false;
            return;
        }

        $this->lastPage = (int) ceil((float) $this->getParam('totalItemsCount') / (float) $this->getParam('itemsPerPage'));
        if ($this->getParam('page') > $this->lastPage) {
            $this->useTemplate = false;
            return;
        }

        $lowerPages = array();
        for ($i = $this->getParam('page') - $this->getParam('displayedPageRange'); $i < $this->getParam('page'); $i++) {
            if ($i < 1) {
                continue;
            }
            $lowerPages[$i] = $this->getPageURL($i);
        }

        $higherPages = array();
        for ($i = $this->getParam('page') + 1; $i <= $this->getParam('page') + $this->getParam('displayedPageRange'); $i++) {
            if ($i > $this->lastPage) {
                break;
            }
            $higherPages[$i] = $this->getPageURL($i);
        }

        $this->setTplVar('firstPage', $this->getFirstPageURL());
        $this->setTplVar('lowerPages', $lowerPages);
        $this->setTplVar('currentPage', $this->getParam('page'));
        $this->setTplVar('higherPages', $higherPages);
        $this->setTplVar('lastPage', $this->getLastPageURL());
        $this->setTplVar('urlTemplate', $this->getParam('urlTemplate'));
    }

    protected function getPageURL($page)
    {
        $params = $this->getParam('params');
        if ($page != 1) {
            $params[$this->getParam('pageParamName')] = $page;
        }

        /// Generate URL using a controller name
        if ($this->getParam('ctl') !== null) {
            $url = Router::getInstance()->URL(
                $this->getParam('ctl'), $this->getParam('action'), $params, $this->getParam('escapeAmps')
            );

        /// Generate URL using a base URL
        } elseif ($this->getParam('urlBase') !== null) {
            $url = $this->getParam('urlBase');
            $glue = '?';
            if (strpos($url, '?') !== false) {
                $glue = '&amp;';
                if ($this->getParam('escapeAmps') !== true) {
                    $glue = '&';
                }
            }
            if ($page > 1) {
                $url .= $glue . $this->getParam('pageParamName') . '=' . $page;
            }

        /// Generate URL using the owner controller
        } else {
            $url = Router::getInstance()->URL(
                Controller::getInstance()->className(),
                Controller::getInstance()->getAction(),
                $this->getParam('escapeAmps')
            );
        }

        return $url;
    }

    protected function getFirstPageURL()
    {
        if ($this->getParam('page') <= $this->getParam('displayedPageRange') + 1) {
            return null;
        }

        return $this->getPageURL(1);
    }

    protected function getLastPageURL()
    {
        if ($this->getParam('page') > $this->lastPage - $this->getParam('displayedPageRange') - 1) {
            return null;
        }

        return $this->getPageURL($this->lastPage);
    }
}
