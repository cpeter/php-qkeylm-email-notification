<?php

/**
 *  !!!!!!!!!!
 *  The mail alert class is not using any nice templateing engine. Feel free to create a pull request for a much nicer email notification. 
 */
  

namespace Cpeter\PhpQkeylmEmailNotification;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Swift_Image;
use Swift_Attachment;

class Alert 
{

    protected static $instance;
    protected $mailer;
    
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
    
    public function send($journal)
    {

        $body = $journal['body'];

        // Create a message. Here we could use tempaltes if we want to.
        $message = Swift_Message::newInstance()
            ->setFrom(array($this->config['from'] => $this->config['from_name']))
            ->setTo(array($this->config['to']  => $this->config['to_name']))
            ->setSubject($this->config['subject']);

        $this->embedImages($message, $body, $journal['images']);
        $this->attachImages($message, $journal['images'], 'large');

        // attach images
        $message->setBody($body, 'text/html');

        // Send the message
        $numSent = $this->mailer->send($message);

        return $numSent;
    }
    
    private function embedImages(&$message, &$body, $images)
    {

        foreach($images as $image_url => $image){
            $body = str_replace(
                '<img class="image-frame" src="' . $image_url . '">',
                $message->embed(Swift_Image::fromPath($image['small'])),
                $body);
        }

    }

    private function attachImages(&$message, $images, $size)
    {
        foreach($images as $image_url => $image){
            $date = date("Y-m-d");
            $ext = pathinfo($image_url, PATHINFO_EXTENSION);
            $message->attach(Swift_Attachment::fromPath($image['large'])->setFilename( $date . '-childcare.' . $ext ));
        }
    }
    
}
