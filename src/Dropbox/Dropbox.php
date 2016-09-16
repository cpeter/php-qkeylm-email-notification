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
     * @param array $connectionParams
     * @return Storage
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getConnection($connectionParams = array())
    {
        if (self::$instance == null) {
            self::$instance = new self();
            self::$instance->client = '';
        }

        return static::$instance;
    }

    /**
     * Upload a single file
     *
     * @param string $source_path
     * @param string $dropbox_path
     */
    public function upload($source_path, $dropbox_path)
    {
        print "Uploading images $source_path, $dropbox_path\n";
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
