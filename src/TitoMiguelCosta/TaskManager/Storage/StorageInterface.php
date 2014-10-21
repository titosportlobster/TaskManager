<?php

namespace TitoMiguelCosta\TaskManager\Storage;

use TitoMiguelCosta\TaskManager\Storage\Criteria;
use TitoMiguelCosta\TaskManager\TaskInterface;

interface StorageInterface
{
    public function retrieve(Criteria $criteria);
    public function store(TaskInterface $task);
    public function delete(TaskInterface $task);
}
