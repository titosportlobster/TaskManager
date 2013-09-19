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

use Sportlobster\Task\Message;

/**
 * The base task handler
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
abstract class BaseHandler
{

    /**
     * Processes the task
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return \Sportlobster\Task\Message
     */
     abstract public function handle(Message $message);
}
