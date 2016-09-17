<?php

namespace Cpeter\PhpQkeylmEmailNotification\Console\Command;

use Cpeter\PhpQkeylmEmailNotification as PhpQkeylmEmailNotification;
use Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration;
use Cpeter\PhpQkeylmEmailNotification\Dropbox\Dropbox;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Swift_TransportException;

class NotifyCommand extends Command
{

    /**
     * Define the command name associated with this class (notify) and the optional input commands (config)
     */
    protected function configure()
    {
        $this
            ->setName('notify')
            ->setDescription('Checks the Qkeylm (childcare) portal and send an email notification of the daily journal')
            ->setDefinition([
                new InputOption(
                    'config',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'A configuration file to configure php-qkeylm-email-notification'
                ),
                new InputOption(
                    'dropbox',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Uploads images to Dropbox'
                )
            ]);
    }

    /**
     * Check if we have already processed the daily journal for today
     * If not login to the qkeylm site and download the journal
     * Download all images
     * Send out the notification email with the images as attachement
     * Mark journal as processed
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);

        $config = $input->getOption('config');
        $configuration = $config ? Configuration::fromFile($config) : Configuration::defaults();

        $qkeylm = new PhpQkeylmEmailNotification\Qkeylm\QkeylmApi($configuration->get("QKEYLM"));
        $storage = PhpQkeylmEmailNotification\Storage::getConnection($configuration->get("DB"));
        $alert = PhpQkeylmEmailNotification\Alert::getInstance($configuration->get("Mailer"));

        $dropbox_enabled = !empty((string)$input->getOption('dropbox'));
        if ($dropbox_enabled) {
            $dropbox = PhpQkeylmEmailNotification\Dropbox\Dropbox::getInstance($configuration->get("Dropbox"));
        }
        
        date_default_timezone_set($configuration->get("TimeZone", "Australia/Sydney"));

        $date = date("Y-m-d");

        // check if current date was already processed
        $date_already_processed = $storage->checkEntry($date);

        if ($date_already_processed) {
            // already processed
            $output->writeln('Page was already processed today. Giving up now.');
        }

        if (!$date_already_processed) {
            $journal = $qkeylm->getDailyJournal($date);

            // send notification and save processed status only if the returned journal is for today
            if ($journal['date'] == $date) {
                $storage->setLatestEntry($date);
                $output->writeln('Sending the notification.');
                try {
                    // send out notification about the version change
                    $alert->send($journal);
                } catch (Swift_TransportException $e) {
                    $output->writeln("Mail notification was not sent. ". $e->getMessage());
                }
                if ($dropbox_enabled) {
                    // upload the images to dropbox
                    try {
                        $output->writeln('Uploading files to Dropbox.');
                        $dropbox->uploadImages($journal);
                    } catch (Exception $e) {
                        $output->writeln("Dropbox upload failed. " . $e->getMessage());
                    }
                }
            } else {
                $output->writeln('No entry for today at this time.');
            }
        }

        $duration = microtime(true) - $startTime;
        $output->writeln('');
        $output->writeln(
            'Time: ' . round($duration, 3) .
            ' seconds, Memory: ' . round(memory_get_peak_usage() / 1024 / 1024, 3) .
            ' MB'
        );
    }
}
