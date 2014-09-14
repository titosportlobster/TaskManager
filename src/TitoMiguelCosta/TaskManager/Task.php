<?php

namespace TitoMiguelCosta\TaskManager;

use TitoMiguelCosta\TaskManager\Storage\StorageInterface;

class Task implements TaskInterface, \ArrayAccess, \IteratorAggregate
{

    protected $name;
    protected $category;
    protected $status;
    protected $storage;
    protected $parameters;
    protected $log;
    protected $createdAt;
    protected $updatedAt;

    public function __construct($name, $status, StorageInterface $storage)
    {
        $this->name = $name;
        $this->storage = $storage;
        $this->category = '';
        $this->status = $status;
        $this->log = array();
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        $this->parameters = array();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name)
    {
        return $this->offsetGet($name);
    }

    public function setParameter($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function hasParameter($name)
    {
        return $this->offsetExists($name);
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->parameters);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->parameters[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->parameters[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->parameters[$offset]);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

}
