<?php

namespace Cpeter\PhpQkeylmEmailNotification\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Cpeter\PhpQkeylmEmailNotification\Console\Command\NotifyCommand;
use Cpeter\PhpQkeylmEmailNotification\Console\Command\NotifyKinderloopCommand;

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

    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct('PHP Qkeylm email notification', self::VERSION);
    }

    /**
     * Display the app help page
     *
     * @return string
     */
    public function getHelp()
    {
        return parent::getHelp();
    }

    /**
     * Add all my commands to the application
     *
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = $this->add(new NotifyCommand());
        $commands[] = $this->add(new NotifyKinderloopCommand());
        return $commands;
    }
}
