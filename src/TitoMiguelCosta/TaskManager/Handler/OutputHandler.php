<?php

namespace TitoMiguelCosta\TaskManager\Handler;

use Symfony\Component\Console\Output\OutputInterface;
use TitoMiguelCosta\TaskManager\TaskInterface;

class OutputHandler implements HandlerInterface
{

    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute(TaskInterface $task)
    {
        $this->output->writeln(sprintf('Executing the task "%s"', $task->getCategory()));
    }

}
