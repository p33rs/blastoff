<?php
namespace p33rs\Config;
class Config {

    private static $config = null;

    /**
     * @param string|null $var
     * @return mixed
     * @throws \Exception
     */
    public static function get($var = null) {
        if (self::$config === null) {
            throw new \Exception('Config not loaded.');
        }
        if (!$var) {
            return self::$config;
        } else if (is_array(self::$config) && array_key_exists($var, self::$config)) {
            return self::$config[$var];
        }
        throw new \Exception('Config param not found: ' . $var);
    }

    /**
     * @param string $filename
     * @param bool $merge
     * @throws \Exception
     */
    public static function read($filename, $merge = false) {
        if (!is_readable($filename)) {
            throw new \Exception('The input file could not be found.');
        }
        /** @var array $config */
        $config = include $filename;
        if (!is_array($config)) {
            throw new \Exception('Unreadable config file.');
        }
        if (!$merge || !self::$config) {
            self::$config = $config;
        } else {
            self::$config += $config;
        }
    }

}