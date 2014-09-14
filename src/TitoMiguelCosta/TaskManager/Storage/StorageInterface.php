<?php

namespace TitoMiguelCosta\TaskManager\Storage;

use TitoMiguelCosta\TaskManager\Storage\Criteria;

interface StorageInterface
{
    public function retrieve(Criteria $criteria);
}
