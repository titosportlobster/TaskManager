<?php

namespace TitoMiguelCosta\TaskManager;

use Symfony\Component\EventDispatcher\EventDispatcher;
use TitoMiguelCosta\TaskManager\Event\HandlerPostBatchEvent;
use TitoMiguelCosta\TaskManager\Event\HandlerPreBatchEvent;
use TitoMiguelCosta\TaskManager\Handler\HandlerInterface;
use TitoMiguelCosta\TaskManager\Storage\StorageInterface;
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
        foreach ($this->storages as $storage) {
            $tasks = $storage->retrieve($criteria);
            $this->executeTasks($storage, $tasks);
        }

    }

    protected function executeTasks(StorageInterface $storage, array $tasks)
    {
        foreach ($this->handlers as $handler) {
            $preBatchEvent = new HandlerPreBatchEvent($handler, $tasks);
            $this->eventDispatcher->dispatch('tmc.task_manager.handler.pre_batch', $preBatchEvent);

            if (!$preBatchEvent->handle()) {
                continue;
            }

            $runnableTasks = $preBatchEvent->getTasks();
            foreach ($runnableTasks as $runnableTask) {
                $handler->execute($runnableTask);
                $storage->store($runnableTask);
            }

            $postBatchEvent = new HandlerPostBatchEvent($handler, $runnableTasks);
            $this->eventDispatcher->dispatch('tmc.task_manager.handler.post_batch', $postBatchEvent);

            if ($postBatchEvent->stopHandling()) {
                break;
            }
        }
    }

}
