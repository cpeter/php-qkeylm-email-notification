<?php

/**
 *  !!!!!!!!!!
 *  The mail alert class is not using any nice templateing engine. Feel free to create a pull request for a much nicer email notification. 
 */
  

namespace Cpeter\PhpQkeylmEmailNotification;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

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
    
    public function send($cms, $version_id, $url)
    {

        $template_values = array('cms' => $cms, 'version_id' => $version_id, 'url' => $url);
        
        // get the values to be sent
        $subject = $this->processTemplate($this->config['subject'], $template_values);
        $body = $this->processTemplate($this->config['body'], $template_values);

        // Create a message. Here we could use tempaltes if we want to.
        $message = Swift_Message::newInstance($subject)
            ->setFrom(array($this->config['from'] => $this->config['from_name']))
            ->setTo(array($this->config['to']  => $this->config['to_name']))
            ->setBody($body)
        ;

        // Send the message
        $numSent = $this->mailer->send($message);

        return $numSent;
    }
    
    private function processTemplate($template, $variables)
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{'. $key . '}', $value, $template);
        }
        return $template;
    }
    
}
