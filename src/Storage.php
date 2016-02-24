<?php

namespace Cpeter\PhpQkeylmEmailNotification;

/**
 * Class Storage
 *
 * Store the last date the data was retrieved successfully.
 * @todo store the email body in the DB as well, and annotate the journals that have the child names in the.
 * @todo nosql (mongodb) storage could be used
 *
 * @package Cpeter\PhpQkeylmEmailNotification
 */
class Storage
{

    /**
     * @var Storage
     */
    protected static $instance;

    /**
     * @var \Doctrine\DBAL\DriverManager
     */
    protected $conn;

    /**
     * Get a singleton connection to the DB
     *
     * @param array $connectionParams
     * @return Storage
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getConnection($connectionParams)
    {
        if (self::$instance == null) {
            if (isset($connectionParams['path'])) {
                $connectionParams['path'] = __DIR__ . '/' . $connectionParams['path'];
            }

            self::$instance = new self();
            self::$instance->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        }

        return static::$instance;
    }

    /**
     * Check the existance of the date in the DB
     *
     * @param string $date
     * @return bool
     */
    public function checkEntry($date)
    {
        // Fetch column as scalar value
        $sth = $this->conn->prepare("SELECT date FROM journals WHERE date =:date");
        $sth->bindValue(":date", $date);
        $sth->execute();
        $date = $sth->fetchColumn();

        return !empty($date);
    }

    /**
     * Store the date in the DB
     *
     * @param string $date
     */
    public function setLatestEntry($date)
    {
        $sth = $this->conn->prepare("REPLACE INTO journals (date) VALUES (:date)");
        $sth->bindValue(":date", $date);
        $sth->execute();
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
