<?php

namespace TitoMiguelCosta\TaskManager\Event;

use Symfony\Component\EventDispatcher\Event;
use TitoMiguelCosta\TaskManager\Handler\HandlerInterface;

class HandlerPreBatchEvent extends Event
{

    protected $handler;
    protected $tasks;
    protected $skipHandler = false;

    public function __construct(HandlerInterface $handler, array $tasks)
    {
        $this->handler = $handler;
        $this->tasks = $tasks;
    }

    public function skipHandler($skip = true)
    {
        $this->skipHandler = (bool) $skip;
    }

    public function handle()
    {
        return false === $this->skipHandler;
    }

    public function setTasks(array $tasks)
    {
        $this->tasks = $tasks;
    }

    public function getTasks()
    {
        return $this->tasks;
    }

}
