<?php

/*
 * This file is part of the SportlobsterTask package.
 *
 * (c) Lobster Media Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sportlobster\Task;

/**
 * Message with the data required for the processing of a task and the status of the task
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class Message
{
    const STATE_OPEN = 0;
    const STATE_IN_PROGRESS = 1;
    const STATE_DONE = 2;
    const STATE_ERROR = 3;
    const STATE_CANCELLED = 4;

    protected $type;

    protected $body;

    protected $state;

    protected $restartCount = 0;

    protected $createdAt;

    protected $updatedAt;

    protected $startedAt;

    protected $completedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->state = self::STATE_OPEN;
    }

    public function __clone()
    {
        $this->state = self::STATE_OPEN;
        $this->startedAt = null;
        $this->completedAt = null;

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @param  array $body
     * @return array
     */
    public function setBody(array $body)
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param array|string $names
     * @param $default
     * @return mixed
     */
    public function getValue($names, $default = null)
    {
        if (!is_array($names)) {
            $names = array($names);
        }

        $body = $this->getBody();
        foreach ($names as $name) {
            if (!isset($body[$name])) {
                return $default;
            }

            $body = $body[$name];
        }

        return $body;
    }

    /**
     * @param  \DateTime $completedAt
     * @return void
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @param  \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt = null)
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
     * @param  string $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  integer $state
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param integer $restartCount
     */
    public function setRestartCount($restartCount)
    {
        $this->restartCount = $restartCount;
    }

    /**
     * @return integer
     */
    public function getRestartCount()
    {
        return $this->restartCount;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
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

    /**
     * @return array
     */
    public static function getStateList()
    {
        return array(
            self::STATE_OPEN => 'open',
            self::STATE_IN_PROGRESS => 'in_progress',
            self::STATE_DONE => 'done',
            self::STATE_ERROR => 'error',
            self::STATE_CANCELLED => 'cancelled'
        );
    }

    /**
     * @param  \DateTime $startedAt
     * @return void
     */
    public function setStartedAt(\DateTime $startedAt = null)
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @return string
     */
    public function getStateName()
    {
        $list = self::getStateList();

        return isset($list[$this->getState()]) ? $list[$this->getState()] : '';
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        return $this->state == self::STATE_IN_PROGRESS;
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return $this->state == self::STATE_ERROR;
    }

    /**
     * @return boolean
     */
    public function isOpen()
    {
        return $this->state == self::STATE_OPEN;
    }
}
