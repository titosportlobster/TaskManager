<?php

namespace TitoMiguelCosta\TaskManager;

interface TaskInterface
{

    const SUCCESS = 0;
    const PROGRESS = 1;
    const ERROR = 2;
    const ABORTED = 3;
    const WAITING = 4;

}
