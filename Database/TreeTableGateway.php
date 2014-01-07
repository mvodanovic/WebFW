<?php

namespace WebFW\Framework\Database;

use WebFW\Framework\Core\Exception;
use WebFW\Framework\Core\Exceptions\NotFoundException;

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
                throw new Exception('parentNodeKeyColumns not set');
            }
            foreach ($this->parentNodeKeyColumns as $parentColumn => $childColumn) {
                if (!array_key_exists($parentColumn, $this->recordData)) {
                    throw new Exception('Invalid parentNodeKeyColumns set');
                }
                if (!array_key_exists($childColumn, $this->recordData)) {
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

    public function getParentNodeKey()
    {
        $key = array();

        foreach ($this->parentNodeKeyColumns as $parentColumn => $childColumn) {
            $key[$childColumn] = $this->recordData[$parentColumn];
        }

        return $key;
    }

    public function getParentNodeKeyColumns()
    {
        return $this->parentNodeKeyColumns;
    }

    public function getMaximumTreeDepth()
    {
        return $this->maximumTreeDepth;
    }

    public function getParentNode()
    {
        if ($this->parentNode === null) {
            $this->parentNode = new static();
            try {
                $this->parentNode->loadBy($this->getParentNodeKey());
            } catch (NotFoundException $e) {
                $this->parentNode = null;
            }
        }

        return $this->parentNode;
    }

    public function delete()
    {
        if ($this->getChildrenNodeCount() === 0) {
            parent::delete();
        }
    }

    abstract public function getChildrenNodes($forceReload = false);
    abstract public function getChildrenNodeCount($forceReload = false);
}
