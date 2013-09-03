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
