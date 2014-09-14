<?php

namespace TitoMiguelCosta\TaskManager\Handler;

use TitoMiguelCosta\TaskManager\TaskInterface;

interface HandlerInterface
{

    public function execute(TaskInterface $task);
}
