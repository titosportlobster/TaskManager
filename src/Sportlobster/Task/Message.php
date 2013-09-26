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

    protected $attrs;

    protected $changedAttrs;

    public function __construct($attrs = array())
    {
        $this->attrs = array(
            'id' => null,
            'type' => null,
            'body' => null,
            'state' => null,
            'restartCount' => null,
            'createdAt' => null,
            'updatedAt' => null,
            'startedAt' => null,
            'completedAt' => null,
        );

        $this->setState(self::STATE_OPEN);
        $this->setRestartCount(0);
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());

        foreach ($attrs as $attr => $value) {
            if (!array_key_exists($attr, $this->attrs)) {
                throw new \InvalidArgumentException(sprintf('Invalid attribute "%s" provided for class %s', $attr, get_class($this)));
            }

            // call the setter
            call_user_method_array(sprintf('set%s', ucfirst($attr)), $this, array($value));
        }

        $this->changedAttrs = array();

    }

    public function __clone()
    {
        $this->setId(null);
        $this->setState(self::STATE_OPEN);
        $this->setRestartCount(0);
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->setStartedAt(null);
        $this->setCompletedAt(null);

        $this->changedAttrs = array();
    }

    public function __call($name, $arguments)
    {
        if ('set' === substr($name, 0, 3)
            && in_array(lcfirst(substr($name, 3)), array_keys($this->attrs))
            && count($arguments) === 1
        ) {
            $attr = lcfirst(substr($name, 3));
            if ($this->attrs[$attr] !== $arguments[0]) {
                $this->changedAttrs[$attr] = $this->attrs[$attr];
            }
            $this->attrs[$attr] = $arguments[0];

            return;
        } elseif ('get' === substr($name, 0, 3)
            && in_array(lcfirst(substr($name, 3)), array_keys($this->attrs))
            && count($arguments) === 0
        ) {
            return $this->attrs[lcfirst(substr($name, 3))];
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method "%s" for instance of class %s.', $name, get_class($this)));
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
        return $this->getState() == self::STATE_IN_PROGRESS;
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return $this->getState() == self::STATE_ERROR;
    }

    /**
     * @return boolean
     */
    public function isOpen()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Returns the changed attributes with teir previous values
     *
     * @return array
     */
    public function getChangedAttributes()
    {
        return $this->changedAttrs;
    }

    /**
     * Returns the names of the changed attributes
     *
     * @return array
     */
    public function getChangedAttributeNames()
    {
        return array_keys($this->changedAttrs);
    }
}
