<?php

namespace Cpeter\PhpQkeylmEmailNotification\Configuration;

use Noodlehaus\Config;

class Configuration
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var \Noodlehaus\Config
     */
    public $config;

    /**
     * @param string|array $file
     * @return \Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration
     */
    public static function fromFile($file)
    {
        $configuration = new Configuration();
        $config = new Config($file);

        $configuration->config = $config;

        return $configuration;
    }

    /**
     * Get a config value for a key
     * 
     * @param string $key 
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * @return \Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration
     */
    public static function defaults()
    {
        $configPath = __DIR__ . "/../../config/";
        return self::fromFile(['?'. $configPath . 'config.dist.yml', '?'. $configPath . 'config.yml']);
    }
}
