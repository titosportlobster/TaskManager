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
            'body' => array(),
            'state' => null,
            'restartCount' => null,
            'createdAt' => null,
            'updatedAt' => null,
            'startedAt' => null,
            'completedAt' => null,
            'logs' => array(),
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

        $this->resetChanges();
    }

    public function __clone()
    {
        $this->setId(null);
        $this->setRestartCount(0);
        $this->setState(self::STATE_OPEN);
        $this->setLogs(array());
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->setStartedAt(null);
        $this->setCompletedAt(null);

        $this->resetChanges();
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
     * Ads a log message
     *
     * The $message should be a string, \Exception instance or array with the following keys:
     *   * time (string) - The log message time. Default is current time in ISO 8601 format (i.e. 2004-02-12T15:19:21+00:00)
     *   * type (string) - The log message type. [debug|info|warning|error]
     *   * message (string) - The log message.
     *   * context (string|array) - The log message context.
     *   * attributes (array) - The Message object attributes excluding the logs. This get added automatically and it will be overridden.
     *
     * @param mixed $messages
     */
    public function log($message)
    {
        if ($message instanceof \Exception) {
            $message = array(
                'type' => 'error',
                'message' => $message->getMessage(),
                'context' => get_class($message).PHP_EOL.$message->getTraceAsString()
            );
        }

        if (is_object($message)) {
            $message = array((string) $message);
        }
        if (!is_array($message)) {
            $message = array('message' => $message);
        }
        if (!isset($message['type'])) {
            $message['type'] = 'info';
        }
        if (!isset($message['time'])) {
            $message['time'] = date('c');
        }
        if (!isset($message['message'])) {
            $message['message'] = '';
        }
        if (!isset($message['context'])) {
            $message['context'] = '';
        }

        $message['attributes'] = $this->toArray();
        $message['attributes']['logs'] = 'REMOVED';

        $logs = $this->getLogs();
        $logs[] = array(
            'type' => $message['type'],
            'time' => $message['time'],
            'attributes' => $message['attributes'],
            'message' => $message['message'],
            'context' => $message['context'],
        );
        $this->setLogs($logs);
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

    /**
     * Resets the changed attributes log
     *
     * NOTE: This method should be called AFTER the message changes have been
     *       persisted.
     */
    public function resetChanges()
    {
        $this->changedAttrs = array();
    }

    /**
     * Returns an array of the message attributes
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();

        foreach (array_keys($this->attrs) as $attr) {
            $value = call_user_func(array($this, 'get'.ucfirst($attr)));
            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::ATOM);
            } elseif (is_object($value)) {
                $value = (string) $value;
            }
            $arr[$attr] = $value;
        }

        return $arr;
    }
}
