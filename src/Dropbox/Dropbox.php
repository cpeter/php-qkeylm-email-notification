<?php

namespace Cpeter\PhpQkeylmEmailNotification\Dropbox;

/**
 * Class DropBox
 *
 * Save the images to dropbox. I used to save this manually but it's just too annoying. :)
 *
 * @package Cpeter\PhpQkeylmEmailNotification
 */

use \Dropbox as dbx;

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
            $accessToken = $options['access_token'];
            $host = dbx\Host::getDefault();
            self::$instance->client = new dbx\Client($accessToken, "QKeylm", 'en', $host);
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
            $source_path = $image['large'];
            $dropbox_path = "/$date/".$date . '-'. ++$img_nr . '-childcare.' . $ext;
            $size = null;
            if (\stream_is_local($source_path)) {
                $size = \filesize($source_path);
            }
            $fp = fopen($source_path, "rb");
            $this->client->uploadFile($dropbox_path, dbx\WriteMode::add(), $fp, $size);
            fclose($fp);
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
