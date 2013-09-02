<?php

namespace Sportlobster\Task;

use Sportlobster\Task\Message;

/**
 * The base task manager
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
abstract class BaseManager
{

    /**
     * Processes the task
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return \Sportlobster\Task\Message
     */
     abstract public function handle(Message $message);

    /**
     * Creates a task message
     *
     * @param string $type The type
     * @param array  $body The body
     *
     * @return \Sportlobster\Task\Message
     */
    public function create($type, array $body)
    {
        $message = new Message();
        $message->setType($type);
        $message->setBody($body);

        return $message;
    }

    /**
     * Publishes a task message
     *
     * @param \Sportlobster\Task\Message $message The message
     */
    public function publish(Message $message)
     {
        $this->handle($message);

        return $message;
     }

    /**
     * Creates and publishes a task message
     *
     * @param string $type The type
     * @param array  $body The body
     *
     * @return mixed
     */
    public function createAndPublish($type, array $body)
    {
        $message = $this->create($type, $body);

        return $this->publish($message);
    }
}
