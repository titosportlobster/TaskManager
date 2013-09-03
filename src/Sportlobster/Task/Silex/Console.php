<?php

/*
 * This file is part of the SportlobsterTask package.
 *
 * (c) Lobster Media Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sportlobster\Task\Silex;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Driver\Connection;
use Sportlobster\Task\DatabaseManager;

/**
 * Console registers the package CLI tasks
 *
 * @author Stanislav Petrov <s.e.petrov@gmail.com>
 */
class Console
{
    /**
     * The CLI tasks namespace
     *
     * @var string
     */
    protected static $namespace = 'sportlobster-task';

    /**
     * Registers console tasks
     *
     * @param \Symfony\Component\Console\Application $console A console application
     * @param \Doctrine\DBAL\Driver\Connection       $conn    A database connection
     */
    public static function register(Application $console = null, Connection $conn = null)
    {
        if (null === $console) {
            $console = new Application('SportlobsterTask', 'n/a');
        }

        if (null !== $conn) {
            self::registerCreateTable($console, $conn);
        }
    }

    protected static function registerCreateTable(Application $console, Connection $conn)
    {
        $console
            ->register(sprintf('%s:create-table', self::$namespace))
            ->setDefinition(array(
                new InputArgument('name', null, 'The table name', DatabaseManager::TABLE),
            ))
            ->setDescription('Creates the task table')
            ->setCode(function (InputInterface $input, OutputInterface $output) use ($conn) {
                $table = $input->getArgument('name');
                $output->writeln(sprintf('Creating table "%s".', $table));
                $sql = <<<EOSQL
                CREATE TABLE `%1\$s` (
                  `type` varchar(32) NOT NULL,
                  `body` longtext NOT NULL COMMENT 'JSON encoded data',
                  `state` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0: open; 1: in progress; 2: done; -1: error; -2: cancelled',
                  `restart_count` tinyint(4) NOT NULL DEFAULT '0',
                  `created_at` datetime NOT NULL,
                  `updated_at` datetime NOT NULL,
                  `started_at` datetime DEFAULT NULL,
                  `completed_at` datetime DEFAULT NULL,
                  KEY `%1\$s_type_idx` (`type`),
                  KEY `%1\$s_type_state_idx` (`type`, `state`)
                )
EOSQL;
                $conn->exec(sprintf($sql, $table));
            })
        ;
    }
}
