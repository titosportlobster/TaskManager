<?php

namespace TitoMiguelCosta\TaskManager\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use TitoMiguelCosta\TaskManager\TaskManager;
use TitoMiguelCosta\TaskManager\Storage\DbalStorage;
use TitoMiguelCosta\TaskManager\Handler\OutputHandler;

class HelloWorldCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('task-manager:hello-world')
            ->setDescription('Hello World')
            ->addArgument(
                'name', InputArgument::OPTIONAL, 'Who do you want to greet?'
            )
            ->addOption(
                'yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = new EventDispatcher();
        $taskManager = new TaskManager($eventDispatcher);

        $dbalStorage = $this->getDbalStorage();
        $taskManager->addStorage($dbalStorage);

        $taskManager->addHandler(new OutputHandler($output));

        $taskManager->handle();
    }

    protected function getDbalStorage()
    {
        $config = new Configuration();
        $connectionParams = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );
        $connection = DriverManager::getConnection($connectionParams, $config);
        $options = ['tableName' => 'task_manager'];

        $dbalStorage = new DbalStorage($connection, $options);

        $this->loadFixtures($connection, $options['tableName']);

        return $dbalStorage;
    }

    protected function loadFixtures($connection, $tableName)
    {
        $data = [
            'category' => 'hello.world',
            'status' => 0,
            'logs' => '[]',
            'parameters' => '[]',
        ];
        $connection->insert($tableName, $data);

        $data = [
            'category' => 'bye_bye.world',
            'status' => 0,
            'logs' => '[]',
            'parameters' => '[]',
        ];
        $connection->insert($tableName, $data);
    }

}
