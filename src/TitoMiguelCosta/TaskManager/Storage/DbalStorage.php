<?php

namespace TitoMiguelCosta\TaskManager\Storage;

use Doctrine\DBAL\Connection;
use TitoMiguelCosta\TaskManager\Storage\Criteria;
use TitoMiguelCosta\TaskManager\Storage\StorageInterface;
use Doctrine\DBAL\Schema\Schema;
use TitoMiguelCosta\TaskManager\Task;
use TitoMiguelCosta\TaskManager\TaskInterface;
use DateTime;

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

    public function retrieve(Criteria $criteria = null)
    {
        $query = $this->buildSql($criteria);


        $results = $this->connection->executeQuery($query);
        $rows = $results->fetchAll();

        $tasks = array();
        foreach ($rows as $result) {
            $task = new Task($result['category'], $result['status']);

            $parameters = json_decode($result['parameters'], true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $task->addParameters($parameters);
            }

            $logs = json_decode($result['logs'], true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $task->setLogs($logs);
            }

            $task->setCreatedAt(new DateTime($result['created_at']));
            $task->setUpdatedAt(new DateTime($result['updated_at']));
            $task->setStartedAt(new DateTime($result['started_at']));
            $task->setFinishedAt(new DateTime($result['finished_at']));
            $task->setIdentifier($result['id']);

            $tasks[$task->getIdentifier()] = $task;
        }

        return $tasks;
    }

    public function store(TaskInterface $task)
    {
        $data = array(
            'status' => $task->getStatus(),
            'category' => $task->getCategory(),
            'parameters' => json_encode($task->getLogs()),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($task->getIdentifier()) {
            $where = array(
                'id' => $task->getIdentifier()
            );

            $this->connection->update($this->options['tableName'], $data, $where);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');

            $this->connection->insert($this->options['tableName'], $data);
        }
    }

    public function delete(TaskInterface $task)
    {
        $query = 'UPDATE %s SET
            deleted_at = ?
            WHERE id = ?
            LIMIT 1';

        $params = array(
            date('Y-m-d H:i:s'),
            $task->getIdentifier()
        );

        $this->connection->executeQuery($query, $params);
    }

    protected function setup()
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(array($this->options['tableName']))) {
            $schema = new Schema();
            $table = $schema->createTable($this->options['tableName']);
            $table->addColumn('id', 'integer', array('unsigned' => true));
            $table->addColumn('category', 'string', array('length' => 128));
            $table->addColumn('status', 'string', array('length' => 50));
            $table->addColumn('logs', 'text', array('notnull' => false));
            $table->addColumn('parameters', 'text', array('notnull' => false));
            $table->addColumn('created_at', 'datetime', array('notnull' => false));
            $table->addColumn('updated_at', 'datetime', array('notnull' => false));
            $table->addColumn('started_at', 'datetime', array('notnull' => false));
            $table->addColumn('finished_at', 'datetime', array('notnull' => false));
            $table->addColumn('deleted_at', 'datetime', array('notnull' => false));
            $table->setPrimaryKey(array('id'));
            $table->addUniqueIndex(array('category'));

            $queries = $schema->toSql($this->connection->getDatabasePlatform());
            foreach ($queries as $query) {
                $this->connection->executeQuery($query);
            }
        }
    }

    protected function buildSql(Criteria $criteria = null)
    {
        $query = 'SELECT
            id, category, status, logs, parameters, created_at, updated_at, started_at, finished_at FROM %s
            %s
            LIMIT %d, %d';

        $where = [
            'deleted_at IS NULL'
        ];

        if (false === $criteria instanceof Criteria) {
            $criteria = new Criteria();
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
            'WHERE ' . implode(' AND ', $where) :
            '';

        $page = $criteria->getPage() ? : 0;
        $count = $criteria->getCount() ? : 5;

        return sprintf($query, $this->options['tableName'], $conditions, $page, $count);
    }
}