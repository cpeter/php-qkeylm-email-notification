<?php

/**
 *  The mail alert class is not using any nice templateing engine. Feel free to create a pull request for a much 
 *  nicer email notification. 
 */
  
namespace Cpeter\PhpQkeylmEmailNotification;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Swift_Image;
use Swift_Attachment;

/**
 * Class Alert
 *
 * Send out daily notification emails
 *
 * @package Cpeter\PhpQkeylmEmailNotification
 */
class Alert
{

    /**
     * @var Alert
     */
    protected static $instance;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * Singleton class init
     *
     * @param array $configuration
     * @return Alert
     */
    public static function getInstance($configuration)
    {
        if (self::$instance == null) {

            // Create the Transport
            $transport = Swift_SmtpTransport::newInstance();

            // Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($transport);
            
            self::$instance = new self();
            self::$instance->mailer = $mailer;
            self::$instance->config = $configuration;
        }

        return static::$instance;
    }

    /**
     * Send out the daily journal to all in the bcc list with the images embeded and attached
     *
     * @param array $journal
     * @return int
     */
    public function send($journal)
    {

        $body = $journal['body'];

        // Create a message. Here we could use tempaltes if we want to.
        $message = Swift_Message::newInstance()
            ->setFrom(array($this->config['from'] => $this->config['from_name']))
            ->setTo(array($this->config['to'] => $this->config['to_name']))
            ->setSubject($this->config['subject']);

        // build mail bcc
        foreach ($this->config['bcc'] as $id => $to) {
            $message->addBcc($to, $this->config['bcc_name'][$id]);
        }

        $this->embedImages($message, $body, $journal['images']);
        $this->attachImages($message, $journal['images'], 'large');

        // attach images
        $message->setBody($body, 'text/html');

        // Send the message
        $numSent = $this->mailer->send($message);

        return $numSent;
    }

    /**
     * Replace image url in the body with embeded images
     *
     * @param Swift_Message $message
     * @param string $body
     * @param array $images
     */
    private function embedImages(&$message, &$body, $images)
    {
        foreach ($images as $image_url => $image) {
            $body = str_replace(
                $image_url,
                $message->embed(Swift_Image::fromPath($image['small'])),
                $body
            );
        }
    }

    /**
     * Attach images to the email
     *
     * @param Swift_Message $message
     * @param array $images
     * @param string $size
     */
    private function attachImages(&$message, $images, $size)
    {
        $img_nr = 0;
        foreach ($images as $image_url => $image) {
            $date = date("Y-m-d");
            $ext = pathinfo($image_url, PATHINFO_EXTENSION);
            $message->attach(
                Swift_Attachment::fromPath($image[$size])->setFilename($date . '-'. ++$img_nr . '-childcare.' . $ext)
            );
        }
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
