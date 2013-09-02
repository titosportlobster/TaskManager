<?php

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
     * @var \Doctrine\DBAL\Driver\Connection
     */
    protected $conn;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(Connection $conn, LoggerInterface $logger = null)
    {
        $this->conn = $conn;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(Message $message)
    {
        $sql = 'INSERT INTO sl_task (type, body, state, restart_count, created_at, updated_at, started_at, completed_at) VALUES (:type, :body, :state, :restart_count, :created_at, :updated_at, :started_at, :completed_at)';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('type', $message->getType());
        $stmt->bindValue('body', json_encode($message->getBody()));
        $stmt->bindValue('state', $message->getState(), 'integer');
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

        $stmt->execute();
    }
}
