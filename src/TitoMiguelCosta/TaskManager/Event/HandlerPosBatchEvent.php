<?php

namespace TitoMiguelCosta\TaskManager\Event;

use Symfony\Component\EventDispatcher\Event;

class HandlerPosBatchEvent extends Event
{

    protected $handler;
    protected $tasks;
    protected $keepHandling = true;

    public function __construct(HandlerInterface $handler, array $tasks)
    {
        $this->handler = $handler;
        $this->tasks = $tasks;
    }

    public function keepHandling($handle = true)
    {
        $this->keepHandling = (bool) $handle;
    }

    public function stopHandling()
    {
        return false === $this->keepHandling;
    }

}
