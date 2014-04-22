<?php

namespace Sportlobster\Task;

/**
 * Task manager with databse backend for processing the task later
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class MyDatabaseManager extends DatabaseManager
{
    /**
     * Constructor, using PHP PDO class instead of Doctrine one
     *
     * @param \PDO                             $conn   The database connection
     * @param string                           $table  The databse table name
     * @param \Psr\Log\LoggerInterface         $logger The logger
     */
    public function __construct(\PDO $conn, $table = null, LoggerInterface $logger = null)
    {
        $this->conn = $conn;
        $this->table = $table ?: self::TABLE;
        $this->logger = $logger;
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
        $stmt->bindValue('state', $message->getState());
        $stmt->bindValue('logs', json_encode($message->getLogs()));
        $stmt->bindValue('restart_count', $message->getRestartCount());
        $stmt->bindValue('created_at', $message->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue('updated_at', $message->getUpdatedAt()->format('Y-m-d H:i:s'));
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
}
