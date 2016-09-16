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
            $accessToken = "CAHhMTWPVyoAAAAAAAAOge86IbiM8-LXJgBeLpPNVty8A2B0F2-nfpOmmZTsd2lG"; 
            try {
                list($accessToken, $host) = dbx\AuthInfo::loadFromJsonFile($nonOptionArgs[0]);
            }
            catch (dbx\AuthInfoLoadException $ex) {
                fwrite(STDERR, "Error loading <auth-file>: ".$ex->getMessage()."\n");
                die;
            }
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
        $this->client = new dbx\Client($accessToken, "examples-$exampleName", $locale, $host);

        $accountInfo = $this->client->getAccountInfo();
        print_r($accountInfo);
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
