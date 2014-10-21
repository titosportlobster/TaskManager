<?php

namespace TitoMiguelCosta\TaskManager;

interface TaskInterface
{

    const COMPLETED = 0;
    const RUNNING = 1;
    const ERROR = 2;
    const ABORTED = 3;
    const WAITING = 4;

    public function getName();
    public function getCategory();
    public function getStatus();
    public function getLogs();
    public function getParameters();

}
