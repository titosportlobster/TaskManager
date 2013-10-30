<?php

/*
 * This file is part of the SportlobsterTask package.
 *
 * (c) Lobster Media Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sportlobster\Task;

use PDO;
use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;
use Sportlobster\Task\BaseManager;
use Sportlobster\Task\Message;

/**
 * Task manager with databse backend for processing the task later
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class DatabaseManager extends BaseManager
{
    /**
     * The default database table name
     *
     * @var string
     */
    const TABLE = 'sportlobster_task';

    /**
     * @var \Doctrine\DBAL\Driver\Connection
     */
    protected $conn;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Doctrine\DBAL\Driver\Connection $conn   The database connection
     * @param string                           $table  The databse table name
     * @param \Psr\Log\LoggerInterface         $logger The logger
     */
    public function __construct(Connection $conn, $table = null, LoggerInterface $logger = null)
    {
        $this->conn = $conn;
        $this->table = $table ?: self::TABLE;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(Message $message)
    {
        $this->insert($message);
    }

    /**
     * Returns task messages
     *
     * @param array   $types  The criteria
     * @param integer $limit  The limit
     * @param integer $offset The offset
     *
     * @return array of \Sportlobster\Task\Message
     */
    public function fetch($criteria = array(), $limit = null, $offset = 0)
    {
        $messages = array();
        $query = $this->getSelectQueryParts($criteria, $limit, $offset);
        $stmt = $this->conn->prepare($query['sql']);
        foreach ($query['params'] as $i => $param) {
            $stmt->bindValue($i + 1, $param['value'], $param['type']);
        }
        $stmt->execute();

        foreach ($stmt->fetchAll() as $task) {
            $messages[] = new Message(array(
                'id'           => $task['id'],
                'type'         => $task['type'],
                'body'         => json_decode($task['body'], true),
                'state'        => $task['state'],
                'logs'         =>json_decode($task['logs'], true),
                'restartCount' => $task['restart_count'],
                'createdAt'    => new \DateTime($task['created_at']),
                'updatedAt'    => new \DateTime($task['updated_at']),
                'startedAt'    => $task['started_at'] ? new \DateTime($task['started_at']) : null,
                'completedAt'  => $task['completed_at'] ? new \DateTime($task['completed_at']) : null,
            ));
        }

        return $messages;
    }

    /**
     * Persists a message
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return boolean TRUE on success or FALSE on failure
     */
    public function save(Message $message)
    {
        if (null === $message->getId()) {
            $status = $this->insert($message);
        } else {
            $status = $this->update($message);
        }

        return $status;
    }

    /**
     * Persists the message changed attributes
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return boolean TRUE on success or FALSE on failure
     */
    public function delete(Message $message)
    {
        if (null === $message->getId()) {
            throw new \LogicException('Can not delete a message which has not been persisted.');
        }

        if (1 == $this->conn->delete($this->table, array('id' => $message->getId()))) {
            return true;
        }

        return false;
    }

    /**
     * Persists the message
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return boolean TRUE on success or FALSE on failure
     */
    protected function insert(Message $message)
    {
        $sql = sprintf('INSERT INTO %s (type, body, state, logs, restart_count, created_at, updated_at, started_at, completed_at) VALUES (:type, :body, :state, :logs, :restart_count, :created_at, :updated_at, :started_at, :completed_at)', $this->table);

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('type', $message->getType());
        $stmt->bindValue('body', json_encode($message->getBody()));
        $stmt->bindValue('state', $message->getState(), 'integer');
        $stmt->bindValue('logs', json_encode($message->getLogs()));
        $stmt->bindValue('restart_count', $message->getRestartCount(), 'integer');
        $stmt->bindValue('created_at', $message->getCreatedAt(), 'datetime');
        $stmt->bindValue('updated_at', $message->getUpdatedAt(), 'datetime');
        if (null === $message->getStartedAt()) {
            $stmt->bindValue('started_at', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue('started_at', $message->getStartedAt(), 'datetime');
        }
        if (null === $message->getCompletedAt()) {
            $stmt->bindValue('completed_at', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue('completed_at', $message->getCompletedAt(), 'datetime');
        }

        if ($status = $stmt->execute()) {
            $message->setId($this->conn->lastInsertId());
            $message->resetChanges();
        }

        return $status;
    }

    /**
     * Persists the message changed attributes
     *
     * @param \Sportlobster\Task\Message $message The message
     *
     * @return boolean TRUE on success or FALSE on failure
     */
    protected function update(Message $message)
    {
        $changed = $message->getChangedAttributeNames();

        if (count($changed) < 1) {
            return;
        }

        $sql = sprintf('UPDATE %s SET %%s WHERE id = :id LIMIT 1', $this->table);
        $set = array();
        $params = array();

        foreach (array(
            // message attribute, database field, data type, value callback (optional))
            array('type', 'type', 'string'),
            array('body', 'body', 'string', function($message) { return json_encode($message->getBody()); }),
            array('state', 'state', 'integer'),
            array('logs', 'logs', 'string', function($message) { return json_encode($message->getLogs()); }),
            array('restartCount', 'restart_count', 'integer'),
            array('createdAt', 'created_at', 'datetime'),
            array('updatedAt', 'updated_at', 'datetime'),
            array('startedAt', 'started_at', 'datetime'),
            array('completedAt', 'completed_at', 'datetime'),
        ) as $config) {
            if (in_array($config[0], $changed)) {
                $set[] = sprintf('%1$s = :%1$s', $config[1]);
                $params[] = $config;
            }
        }
        $params[] = array('id', 'id', 'integer');

        $sql = sprintf($sql, implode(', ', $set));

        $stmt = $this->conn->prepare($sql);

        // logging context
        $context = array('method' => __METHOD__, 'params' => array());

        foreach ($params as $config) {
            $value = empty($config[3])
                ? call_user_func(array($message, sprintf('get%s', ucfirst($config[0])))) // use the getter
                : call_user_func($config[3], $message) // use the callback
            ;
            $stmt->bindValue(
                $config[1],
                $value,
                null === $value ? PDO::PARAM_NULL : $config[2]
            );
            $context['params'][] = $value;
        }

        $this->logger && $this->logger->info($sql, $context);

        if ($status = $stmt->execute()) {
            $message->resetChanges();
        }

        return $status;
    }

    protected function getSelectQueryParts($criteria = array(), $limit = null, $offset = 0)
    {
        $sql    = sprintf('SELECT id, type, body, state, logs, restart_count, created_at, updated_at, started_at, completed_at FROM %s', $this->table);
        $where  = array();
        $params = array();

        foreach (array(
            'id'            => 'integer',
            'type'          => 'string',
            'state'         => 'integer',
            'restart_count' => 'integer',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'started_at'    => 'datetime',
            'completed_at'  => 'datetime',
        ) as $field => $type) {
            if (array_key_exists($field, $criteria)) {
                if (!is_array($criteria[$field])) {
                    $where[]  = sprintf('%s = ?', $field);
                    $params[] = array(
                        'value' => $criteria[$field],
                        'type'  => $type,
                    );
                } else {
                    foreach (array(
                        'in'     => '%s IN (%s)',
                        'not_in' => '%s NOT IN(%s)',
                    ) as $operator => $condition) {
                        if (array_key_exists($operator, $criteria[$field])) {
                            if (!is_array($criteria[$field][$operator])) {
                                $criteria[$field][$operator] = array($criteria[$field][$operator]);
                            }
                            if (count($criteria[$field][$operator])) {
                                $where[] = sprintf($condition, $field, trim(str_repeat('?, ', count($criteria[$field][$operator])), ', '));
                                foreach ($criteria[$field][$operator] as $value) {
                                    $params[] = array(
                                        'value' => $value,
                                        'type'  => $type,
                                    );
                                }
                            }
                        }
                    }

                    foreach (array(
                        'greater_than'       => '%s > ?',
                        'greater_than_equal' => '%s >= ?',
                        'less_than'          => '%s < ?',
                        'less_than_equal'    => '%s <= ?',
                    ) as $operator => $condition) {
                        if (array_key_exists($operator, $criteria[$field])) {
                            if (!is_array($criteria[$field][$operator])) {
                                $criteria[$field][$operator] = array($criteria[$field][$operator]);
                            }
                            foreach ($criteria[$field][$operator] as $value) {
                                $where[]  = sprintf($condition, $field);
                                $params[] = array(
                                    'value' => $value,
                                    'type'  => $type,
                                );
                            }
                        }
                    }
                }
            }
        }

        // convert the datetime parameters to objects
        foreach ($params as $i => $param) {
            if ('datetime' == $param['type'] && !$param['value'] instanceof \DateTime) {
                $params[$i]['value'] = new \DateTime($param['value']);
            }
        }

        // WHERE
        if (count($where) > 0) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }

        // ORDER BY
        $sql .= ' ORDER BY created_at, id ASC';

        // LIMIT OFFSET
        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = array(
                'value' => $limit,
                'type'  => 'integer'
            );
            $params[] = array(
                'value' => $offset,
                'type'  => 'integer'
            );
        }

        $this->logger && $this->logger->info($sql, array(
            'method' => __METHOD__,
            'criteria' => $criteria,
            'limit' => $limit,
            'offset' => $offset,
        ));

        return array('sql' => $sql, 'params' => $params);
    }
}
