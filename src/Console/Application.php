<?php

namespace Cpeter\PhpQkeylmEmailNotification\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cpeter\PhpQkeylmEmailNotification\Console\Command\NotifyCommand;

class Application extends SymfonyApplication
{

    const VERSION = '@package_version@';
    
    /**
     * A method used to test whether this class is autoloaded.
     *
     * @return bool
     *
     * @see \PCs\PhpQkeylmEmailNotification\Test\DummyTest
     */
    public function autoloaded ()
    {
        return   true;
    }
    
    public function __construct()
    {
        parent::__construct('PHP Qkeylm email notification', self::VERSION);
    }
    
    public function getHelp()
    {
        return parent::getHelp();
    }
    
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = $this->add(new NotifyCommand());
        return $commands;
    }
}
