<?php

namespace TitoMiguelCosta\TaskManager\Handler;

use Symfony\Component\Console\Output\OutputInterface;
use TitoMiguelCosta\TaskManager\TaskInterface;
use DateTime;

class OutputHandler implements HandlerInterface
{

    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute(TaskInterface $task)
    {
        $task->setStartedAt(new DateTime());
        $task->setStatus(TaskInterface::RUNNING);
        $this->output->writeln(sprintf('Executing the task "%s"', $task->getCategory()));
        $task->setFinishedAt(new DateTime());
        $task->setStatus(TaskInterface::COMPLETED);
    }

}
