<?php

namespace Sportlobster\Task;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Sportlobster\Task\Message;

/**
 * Task event
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class Event extends BaseEvent
{
    /**
     * @var \Sportlobster\Task\Message
     */
    protected $message;

    /**
     * Constructor
     *
     * @param \Sportlobster\Task\Message $message The task message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Returns the task message
     *
     * @return \Sportlobster\Task\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
