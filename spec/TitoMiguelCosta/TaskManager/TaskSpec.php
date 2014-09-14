<?php

namespace spec\TitoMiguelCosta\TaskManager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use TitoMiguelCosta\TaskManager\Storage\StorageInterface;
use TitoMiguelCosta\TaskManager\TaskInterface;

class TaskSpec extends ObjectBehavior
{

    public function let(StorageInterface $storage)
    {
        $this->beConstructedWith('example', TaskInterface::SUCCESS, $storage);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('TitoMiguelCosta\TaskManager\Task');
    }

    public function it_get_name()
    {
        $this->getName()
                ->shouldReturn('example');
    }

}
