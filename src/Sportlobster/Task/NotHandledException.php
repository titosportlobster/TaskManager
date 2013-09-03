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

use Exception;
use Sportlobster\Task\Message;

/**
 * Task not handled exception
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class NotHandledException extends Exception
{
    /**
     * @var \Sportlobster\Task\Message
     */
    protected $taskMessage;

    /**
     *
     * @param \Sportlobster\Task\Message $taskMessage The task message
     * @param string                                  The message
     * @param int                                     The code
     * @param \Exception                              The previous exception
     */
    public function __construct(Message $taskMessage, $message = '', $code = 0, Exception $previous = null)
    {
        $this->taskMessage = $taskMessage;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the task message
     *
     * @return \Sportlobster\Task\Message
     */
    public function getTaskMessage()
    {
        return $this->taskMessage;
    }
}
