<?php

namespace Cpeter\PhpQkeylmEmailNotification;

class Storage
{

    protected static $instance;
    protected $conn;
    
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

    public function checkEntry($date)
    {
        // Fetch column as scalar value
        $sth = $this->conn->prepare("SELECT date FROM journals WHERE date =:date");
        $sth->bindValue(":date", $date);
        $sth->execute();
        $date = $sth->fetchColumn();

        return !empty($date);
    }

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
