<?php

namespace TitoMiguelCosta\TaskManager;

use DateTime;
use TitoMiguelCosta\TaskManager\Exception\InvalidTaskStatusException;
use TitoMiguelCosta\TaskManager\TaskInterface;

class Task implements TaskInterface, \ArrayAccess, \IteratorAggregate
{

    protected $category;
    protected $status;
    protected $parameters;
    protected $createdAt;
    protected $updatedAt;
    protected $startedAt;
    protected $finishedAt;
    protected $log;
    protected $identifier;

    public function __construct($category = '', $status = TaskInterface::WAITING)
    {
        $this->category = $category;
        $this->setStatus($status);
        $this->log = [];
        $this->parameters = [];
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->identifier = null;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if (!in_array($status, array(
            TaskInterface::ABORTED,
            TaskInterface::ERROR,
            TaskInterface::RUNNING,
            TaskInterface::COMPLETED,
            TaskInterface::WAITING
        ))
        ) {
            throw new InvalidTaskStatusException(sprintf('The status %s is not supported', $status));
        }

        $this->status = $status;
    }

    public function addLog($message, $index = null)
    {
        $index = null === $index ? date('Y-m-d H:i:s') : $index;

        $this->log[$index] = $message;
    }

    public function setLogs(array $logs)
    {
        $this->logs = $logs;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name)
    {
        return $this->hasParameter($name) ? $this->offsetGet($name) : null;
    }

    public function setParameter($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function hasParameter($name)
    {
        return $this->offsetExists($name);
    }

    public function addParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param mixed $finishedAt
     */
    public function setFinishedAt(DateTime $finishedAt)
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $startedAt
     */
    public function setStartedAt(DateTime $startedAt)
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return mixed
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($id)
    {
        $this->identifier = $id;
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
