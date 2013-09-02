<?php

namespace Sportlobster\Task;

use Exception;
use Sportlobster\Task\Message;

/**
 * Task handling exception
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class HandlingException extends Exception
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
