<?php

namespace TitoMiguelCosta\TaskManager;

use Symfony\Component\EventDispatcher\EventDispatcher;
use TitoMiguelCosta\TaskManager\Event\HandlerPosBatchEvent;
use TitoMiguelCosta\TaskManager\Event\HandlerPreBatchEvent;
use TitoMiguelCosta\TaskManager\HandlerInterface;
use TitoMiguelCosta\TaskManager\StorageInterface;
use TitoMiguelCosta\TaskManager\Storage\Criteria;

class TaskManager
{

    protected $storages = array();
    protected $handlers = array();
    protected $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addStorage(StorageInterface $storage)
    {
        $this->storages[] = $storage;
    }

    public function addStorages(array $storages)
    {
        foreach ($storages as $storage) {
            $this->addStorage($storage);
        }
    }

    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    public function addHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    public function handle(Criteria $criteria = null)
    {
        $tasks = $this->retrieveTasks($criteria);

        $this->executeTasks($tasks);
    }

    protected function retrieveTasks(Criteria $criteria = null)
    {
        $tasks = array();
        foreach ($this->storages as $storage) {
            $tasks = array_merge($tasks, $storage->retrieve($criteria));
        }

        return $tasks;
    }

    protected function executeTasks(array $tasks)
    {
        foreach ($this->handlers as $handler) {
            $preBatchEvent = new HandlerPreBatchEvent($handler, $tasks);
            $this->eventDispatcher->dispatch('tmc.task_manager.handler.pre_batch', $preBatchEvent);

            if ($preBatchEvent->handle()) {
                continue;
            }

            $runnableTasks = $preBatchEvent->getTasks();
            foreach ($runnableTasks as $runnableTask) {
                $handler->execute($runnableTask);
            }

            $postBatchEvent = new HandlerPostBatchEvent($handler, $runnableTasks);
            $this->eventDispatcher->dispatch('tmc.task_manager.handler.post_batch', $postBatchEvent);

            if ($postBatchEvent->stopHandling()) {
                break;
            }
        }
    }

}
