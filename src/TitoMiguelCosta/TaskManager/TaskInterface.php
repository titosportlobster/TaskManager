<?php

namespace TitoMiguelCosta\TaskManager;

interface TaskInterface
{

    const COMPLETED = 0;
    const RUNNING = 1;
    const ERROR = 2;
    const ABORTED = 3;
    const WAITING = 4;

}
