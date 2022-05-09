<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 12:59
 */

namespace FMDataAPI;

use \Exception;

class Settings
{
    const DATA_API_PARAMETERS = ['server', 'port', 'database', 'username', 'password', 'verify', 'locale', 'cache'];

    protected $server;
    protected $port;
    protected $database;
    protected $username;
    protected $password;
    protected $verify;
    protected $locale;
    protected $cache;

    /**
     * @param array $array
     *
     * @return Settings
     * @throws Exception
     */
    public static function CreateFromArray(array $array)
    {
        $settings = new static();
        foreach(static::DATA_API_PARAMETERS as $parameter) {
            if(!array_key_exists($parameter, $array)) {
                throw new Exception(sprintf('Missing parameter %s', $parameter));
            }

            $settings->$parameter = $array[$parameter];
        }

        return $settings;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return boolean
     */
    public function getDoNotVerify()
    {
        return $this->verify;
    }

    /**
    * @return boolean
    */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * @return boolean
     */
    public function getCache()
    {
        return (bool)$this->cache;
    }
}