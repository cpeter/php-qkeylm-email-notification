<?php

namespace Cpeter\PhpQkeylmEmailNotification\Console\Command;

use Cpeter\PhpQkeylmEmailNotification as PhpQkeylmEmailNotification;
use Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration;
use Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Swift_TransportException;


class NotifyCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('notify')
            ->setDescription('Checks the Qkeylm (childcare) portal and send an email notification of the daily journal')
            ->setDefinition([
                new InputOption('config', null, InputOption::VALUE_REQUIRED, 'A configuration file to configure php-qkeylm-email-notification')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);

        $config = $input->getOption('config');
        $configuration = $config ? Configuration::fromFile($config) : Configuration::defaults();

        $options = $configuration->get("QKEYLM");
        $qkeylm = new \Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi($options);
        $storage = PhpQkeylmEmailNotification\Storage::getConnection($configuration->get("DB"));
        $alert = PhpQkeylmEmailNotification\Alert::getInstance($configuration->get("Mailer"));

        $journal = $qkeylm->getDailyJournal();
        try{
            // send out notification about the version change
            $alert->send($journal);
        }catch(Swift_TransportException $e){
            $output->writeln("Mail notification was not sent. ". $e->getMessage());
        }
        print_r($journal);
//
//        foreach($configuration->get("CMS") as $cms => $cms_options){
//            // get version number from the website
//            $version_id = $parser->parse($cms, $cms_options);
//
//            // get version number stored in local storage
//            $stored_version = $storage->getVersion($cms);
//
//            // if the two versions are different send out a mail and store the new value in the db
//            if ($version_id != false && $version_id != $stored_version){
//                $storage->putVersion($cms, $version_id);
//
//                try{
//                    // send out notification about the version change
//                    $alert->send($cms, $version_id, $cms_options['url']);
//                }catch(Swift_TransportException $e){
//                    $output->writeln("Mail notification was not sent. ". $e->getMessage());
//                }
//            }
//
//            $output->writeln("$cms Version: " . $version_id. ' -> ' . $stored_version);
//        }
        
        $duration = microtime(true) - $startTime;
        $output->writeln('');
        $output->writeln('Time: ' . round($duration, 3) . ' seconds, Memory: ' . round(memory_get_peak_usage() / 1024 / 1024, 3) . ' MB');
    }
    
}