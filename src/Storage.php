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

    public function getVersion($cms)
    {
        // Fetch column as scalar value
        $sth = $this->conn->prepare("SELECT version FROM versions WHERE name = :name");
        $sth->bindValue(":name", $cms);
        $sth->execute();
        $version = $sth->fetchColumn();

        return $version;
    }

    public function putVersion($cms, $version)
    {
        $sth = $this->conn->prepare("REPLACE  INTO versions (name, version) VALUES (:name, :version)");
        $sth->bindValue(":name", $cms);
        $sth->bindValue(":version", $version);
        $sth->execute();
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected  function __construct(){
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
