<?php

namespace TitoMiguelCosta\TaskManager\Storage;

use Doctrine\DBAL\Connection;
use TitoMiguelCosta\TaskManager\Storage\Criteria;
use TitoMiguelCosta\TaskManager\Storage\StorageInterface;
use TitoMiguelCosta\TaskManager\Task;

class DbalStorage implements StorageInterface
{

    protected $connection;
    protected $options;

    public function __construct(Connection $connection, array $options = array())
    {
        $this->connection = $connection;

        $defaults = array(
            'tableName' => 'task'
        );

        $this->options = array_merge($defaults, $options);

        $this->setup();
    }

    protected function setup()
    {
        $schemaManager = $this->connection->getSchemaManager();
        $tables = $schemaManager->getTables();

        $tableExists = false;
        foreach ($tables as $table) {
            if ($this->options['tableName'] === $table->getName()) {
                $tableExists = true;
                break;
            }
        }
        if (false === $tableExists){
            
        }
    }

    public function retrieve(Criteria $criteria)
    {
        $query = $this->buildSql($criteria);

        $results = $this->connection->executeQuery($query);

        $tasks = array();
        foreach ($results as $result) {
            $task = new Task($result['name']);
            $task->setStatus($result['status']);
            $tasks[] = $task;
        }

        return $tasks;
    }

    protected function buildSql(Criteria $criteria)
    {
        $query = "SELECT 
            id, name, status FROM %s
            %s
            LIMIT %d, %d
        ";

        $where = array();
        $name = $criteria->getName();
        if ($name) {
            $where[] = sprintf('name = "%s"', $name);
        }
        $status = $criteria->getStatus();
        if ($status) {
            $where[] = sprintf('status = %d', $status);
        }
        $conditions = count($where) ?
                'WHERE ' . explode(' AND ', $where) :
                '';

        $page = $criteria->getPage() ? : 1;
        $count = $criteria->getCount() ? : 5;

        return sprintf($query, $this->options['tableName'], $conditions, $page, $count);
    }

}
