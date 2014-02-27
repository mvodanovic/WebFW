<?php

namespace mvodanovic\WebFW\CMS\Classes;

use mvodanovic\WebFW\Core\Classes\HTML\Link;
use mvodanovic\WebFW\Core\Route;
use mvodanovic\WebFW\Database\TableGateway;

class ListRowAction
{
    protected $link = null;
    protected $route = null;
    protected $handlerFunction = null;
    protected $setPrimaryKeyInDataAttribute;

    public function __construct(Link $link = null, Route $route = null, $setPrimaryKeyInDataAttribute = false)
    {
        $this->link = $link;
        $this->route = $route;
        $this->setPrimaryKeyInDataAttribute = $setPrimaryKeyInDataAttribute;
    }

    public function getLink(TableGateway $tableGateway = null, $params = array())
    {
        if (!($this->link instanceof Link)) {
            return null;
        }

        if ($this->route instanceof Route) {
            if ($tableGateway instanceof TableGateway) {
                $params = array_merge($tableGateway->getPrimaryKeyValues(), $params);
            }
            $this->route->addParams($params);
            $this->link->setAttribute('href', $this->route->getURL(false));
        }

        if ($this->setPrimaryKeyInDataAttribute === true && $tableGateway instanceof TableGateway) {
            $dataParams = $tableGateway->getPrimaryKeyValues(false);
            $this->link->setAttribute('data-primary-key', json_encode($dataParams, JSON_FORCE_OBJECT));
            $this->link->setAttribute('data-caption', $tableGateway->getCaption());
        }

        return $this->link;
    }

    public function setHandlerFunction($handlerFunction)
    {
        $this->handlerFunction = $handlerFunction;
    }

    public function getHandlerFunction()
    {
        return $this->handlerFunction;
    }
}
