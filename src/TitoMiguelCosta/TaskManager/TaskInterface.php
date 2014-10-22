<?php

namespace TitoMiguelCosta\TaskManager;

use DateTime;

interface TaskInterface
{

    const COMPLETED = 0;
    const RUNNING = 1;
    const ERROR = 2;
    const ABORTED = 3;
    const WAITING = 4;

    public function getCategory();

    public function getStatus();
    
    public function setStatus($status);

    public function getLogs();

    public function getParameters();

    public function getParameter($name);

    public function hasParameter($name);

    public function setParameter($name, $value);

    public function addParameters(array $parameters);

    public function setFinishedAt(DateTime $finishedAt);

    public function getFinishedAt();

    public function setCreatedAt(DateTime $createdAt);

    public function getCreatedAt();

    public function setUpdatedAt(DateTime $updatedAt);

    public function getUpdatedAt();

    public function setStartedAt(DateTime $startedAt);

    public function getStartedAt();

    public function getIdentifier();

    public function setIdentifier($id);

}
