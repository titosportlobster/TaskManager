<?php

namespace TitoMiguelCosta\TaskManager\Storage;

use Doctrine\DBAL\Connection;
use TitoMiguelCosta\TaskManager\Storage\Criteria;
use TitoMiguelCosta\TaskManager\Storage\StorageInterface;
use TitoMiguelCosta\TaskManager\Task;
use TitoMiguelCosta\TaskManager\TaskInterface;

class DbalStorage implements StorageInterface
{

    protected $connection;
    protected $options;

    public function __construct(Connection $connection, array $options = array())
    {
        $this->connection = $connection;

        $defaults = array(
            'tableName' => 'task_manager'
        );

        $this->options = array_merge($defaults, $options);

        $this->setup();
    }

    public function retrieve(Criteria $criteria)
    {
        $query = $this->buildSql($criteria);

        $results = $this->connection->executeQuery($query);

        $tasks = array();
        foreach ($results as $result) {
            $task = new Task($result['name'], $result['status'], $result['category']);
            $tasks[] = $task;
        }

        return $tasks;
    }

    public function store(TaskInterface $task)
    {
        $data = array(
            'status' => $task->getStatus(),
            'category' => $task->getCategory(),
            'parameters' => json_encode($task->getLogs())
        );
        $this->connection->insert($this->options['tableName'], $data);


        $where = array(
            'name' => $task->getName(),
            'category' => $task->getCategory()
        );
        $this->connection->update($this->options['tableName'], $data, $where);
    }

    public function delete(TaskInterface $task)
    {
        $query = "UPDATE %s SET
            deleted_at = ?
            WHERE name = ? AND category = ?";

        $params = array(
            date('Y-m-d H:i:s'),
            $task->getName(),
            $task->getCategory()
        );

        $this->connection->executeQuery($query, $params);
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

        if (false === $tableExists) {
            $query = 'CREATE TABLE IF NOT EXISTS %s (
                id BIGINT(20) AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(128),
                status varchar(50) NOT NULL,
                log TEXT,
                parameters TEXT,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                started_at datetime DEFAULT NULL,
                finished_at datetime DEFAULT NULL,
                deleted_at datetime DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY name_category (name, category),
                KEY category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

            $this->connection->executeQuery(sprintf($query, $this->options['tableName']));
        }
    }

    protected function buildSql(Criteria $criteria)
    {
        $query = "SELECT 
            id, name, category, status, log, parameters, created_at, updated_at, started_at, finished_at, deleted_at FROM %s
            %s
            LIMIT %d, %d";

        $where = array();
        $name = $criteria->getName();
        if (null !== $name) {
            $where[] = sprintf('name = "%s"', $name);
        }
        $status = $criteria->getStatus();
        if (null !== $status) {
            $where[] = sprintf('status = "%s"', $status);
        }
        $category = $criteria->getCategory();
        if (null !== $category) {
            $where[] = sprintf('category = "%s"', $category);
        }
        $conditions = count($where) ?
            'WHERE ' . explode(' AND ', $where) :
            '';

        $page = $criteria->getPage() ? : 1;
        $count = $criteria->getCount() ? : 5;

        return sprintf($query, $this->options['tableName'], $conditions, $page, $count);
    }

}