<?php

namespace Cpeter\PhpQkeylmEmailNotification\Dropbox;

/**
 * Class DropBox
 *
 * Save the images to dropbox. I used to save this manually but it's just too annoying. :)
 *
 * @package Cpeter\PhpQkeylmEmailNotification
 */

use \Kunnu\Dropbox\DropboxApp;
use \Kunnu\Dropbox\Dropbox as dropboxapi;

class Dropbox
{

    /**
     * @var Storage
     */
    protected static $instance;

    /**
     * @var \Dropbox;
     */
    protected $client;

    /**
     * Get a singleton connection to dropbox
     *
     * @param array $options
     * @return Dropbox
     */
    public static function getInstance($options = array())
    {
        if (self::$instance == null) {
            self::$instance = new self();

            $client_id = $options['client_id'];
            $client_secret = $options['client_secret'];
            $accessToken = $options['access_token'];

            $app = new DropboxApp($client_id, $client_secret, $accessToken);
            self::$instance->client = new dropboxapi($app);
        }

        return static::$instance;
    }

    /**
     * Upload images from the journal
     *
     * @param string $source_path
     * @param string $dropbox_path
     */
    public function uploadImages($journal)
    {

        $img_nr = 0;
        foreach ($journal['images'] as $image_url => $image) {

            $date = $journal['date'];
            $ext = pathinfo($image_url, PATHINFO_EXTENSION);
            // had to add .jpg since the client will add the extension automatically for some unknown reason
            $source_path = $image['large'].'.jpg';
            $dropbox_path = "/$date/".$date . '-'. ++$img_nr . '-childcare.' . $ext;

            $this->client->upload($source_path, $dropbox_path, ['autorename' => true]);

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
