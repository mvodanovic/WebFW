<?php

namespace WebFW\Database;

use \WebFW\Core\Exception;

abstract class TreeTableGateway extends TableGateway
{
    protected $nodeLevelColumn = null;
    protected $parentNodeKeyColumns = null;
    protected $maximumTreeDepth = null;
    protected $parentNode = null;

    public function __construct()
    {
        parent::__construct();

        if ($this->nodeLevelColumn === null) {
            throw new Exception('nodeLevelColumn not set');
        } elseif (!array_key_exists($this->nodeLevelColumn, $this->recordData)) {
            throw new Exception('Invalid nodeLevelColumn set');
        }

        if ($this->parentNodeKeyColumns === null) {
            throw new Exception('parentNodeKeyColumns not set');
        } else {
            if (!is_array($this->parentNodeKeyColumns)) {
                $this->parentNodeKeyColumns = array($this->parentNodeKeyColumns);
            }
            foreach ($this->parentNodeKeyColumns as $column) {
                if (!array_key_exists($column, $this->recordData)) {
                    throw new Exception('Invalid parentNodeKeyColumns set');
                }
            }
        }

        if ($this->maximumTreeDepth === null) {
            throw new Exception('maximumTreeDepth not set');
        } elseif (!is_int($this->maximumTreeDepth) || $this->maximumTreeDepth <= 0) {
            throw new Exception('Invalid maximumTreeDepth set');
        }
    }

    public function getCurrentNodeLevel()
    {
        return $this->recordData[$this->nodeLevelColumn];
    }

    public function getParentNodeKey($forceKeyArray = false)
    {
        $key = array();

        foreach ($this->parentNodeKeyColumns as $column) {
            $key[$column] = $this->recordData[$column];
        }

        if (count($key) <= 1 && $forceKeyArray === false) {
            $key = array_pop($key);
        }

        return $key;
    }

    public function getMaximumTreeDepth()
    {
        return $this->maximumTreeDepth;
    }

    public function getParentNode()
    {
        if ($this->parentNode === null) {
            $this->parentNode = new $this();
            try {
                $this->parentNode->loadBy($this->getParentNodeKey(true));
            } catch (Exception $e) {
                $this->parentNode = null;
            }
        }

        return $this->parentNode;
    }

    abstract public function getChildrenNodes();
}
