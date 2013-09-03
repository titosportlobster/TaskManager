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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Sportlobster\Task\BaseManager;
use Sportlobster\Task\Event;
use Sportlobster\Task\Message;

/**
 * Task manager for real time processing of the tasks
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class RealtimeManager extends BaseManager
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var boolean
     */
    protected $strict;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher
     * @param boolean                                                     $strict     Whether the processing of the task is mandatory
     * @param \Psr\Log\LoggerInterface                                    $logger     The logger
     */
    public function __construct(EventDispatcherInterface $dispatcher, $strict = false, LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->strict = $strict;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(Message $message)
    {
        $event = new Event($message);

        try {
            $this->dispatcher->dispatch($message->getType(), $event);
        } catch (Exception $e) {
            $message->setState(Message::STATE_ERROR);
            $message->setCompletedAt(new \DateTime());
            $msg = sprintf('An exception occurred while handling task "%s".', $message->getType());

            throw new HandlingException($message, $msg, 0, $e);
        }

        if ($this->strict && $message->getState() === Message::STATE_OPEN) {
            $msg = sprintf('The task "%s" has not been handled.', $message->getType());

            throw new NotHandledException($message, $msg);
        }
    }
}
