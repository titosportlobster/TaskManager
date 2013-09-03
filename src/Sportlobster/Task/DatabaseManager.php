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
        $sql = sprintf('INSERT INTO %s (type, body, state, restart_count, created_at, updated_at, started_at, completed_at) VALUES (:type, :body, :state, :restart_count, :created_at, :updated_at, :started_at, :completed_at)', $this->table);

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
