<?php

namespace Smrtr\Roddick;

/**
 * Class PhpServerUtils provides common utility methods and constants for the roddick server commands.
 *
 * @package Smrtr\Roddick
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class PhpServerUtils
{
    const DEFAULT_HOST = "127.0.0.1";

    const DEFAULT_PORT = 8080;

    /**
     * Get the correct server address given the address and port parameters.
     *
     * @param string      $host
     * @param string|null $port
     *
     * @return string
     */
    public static function getServerAddress($host, $port)
    {
        if (0 < (int) $port) {
            // override port
            $port = (int) $port;
            if (false === $pos = strrpos($host, ':')) {
                return $host.":$port";
            } else {
                return substr($host, 0, $pos).":$port";

            }
        } else {
            if (false === strpos($host, ':')) {
                return $host.":".static::DEFAULT_PORT;
            } else {
                return $host;
            }
        }
    }

    /**
     * Get the lock file path for the given server address.
     *
     * @param string $address Of the form "<host>:<port>"
     *
     * @return string
     */
    public static function getLockFile($address)
    {
        return sys_get_temp_dir() . "/roddick-" . str_replace([".", ":"], "-", $address);
    }

    /**
     * Find out if a sever is running at the given address.
     *
     * @param string $address Of the form "<host>:<port>"
     *
     * @return bool True if the server is running, false otherwise.
     */
    public static function getServerStatus($address)
    {
        list($host, $port) = explode(":", $address);

        if (false !== $handle = @fsockopen($host, $port, $errno, $errstr, 1)) {
            fclose($handle);
            return true;
        }

        return false;
    }
}
