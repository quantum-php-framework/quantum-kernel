<?php

namespace Quantum;

/**
 * Class Uuid
 * @package Quantum
 */
class Uuid
{

    /**
     * Generate v3 UUID
     *
     * Version 3 UUIDs are named based. They require a namespace (another
     * valid UUID) and a value (the name). Given the same namespace and
     * name, the output is always the same.
     *
     * @param	uuid	$namespace
     * @param	QString	$name
     * @return QString
     */
    public static function v3($namespace, $name)
    {
        if(!self::is_valid($namespace))
            return false;
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);
        // Binary Value
        $nstr = '';
        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2)
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }
        // Calculate hash value
        $hash = md5($nstr . $name);
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }


    /**
     *
     * Generate v4 UUID
     *
     * Version 4 UUIDs are pseudo-random.
     * @return QString
     */
    public static function v4()
    {
        $bytes = random_bytes(16); // on php5 we use paragonie/random_compat
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $id = str_split(bin2hex($bytes), 4);
        return "{$id[0]}{$id[1]}-{$id[2]}-{$id[3]}-{$id[4]}-{$id[5]}{$id[6]}{$id[7]}";
    }

    /**
     * Generate v5 UUID
     *
     * Version 5 UUIDs are named based. They require a namespace (another
     * valid UUID) and a value (the name). Given the same namespace and
     * name, the output is always the same.
     *
     * @param	uuid	$namespace
     * @param	QString	$name
     * @return QString
     */
    public static function v5($namespace, $name)
    {
        if(!self::is_valid($namespace)) return false;
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);
        // Binary Value
        $nstr = '';
        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2)
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }
        // Calculate hash value
        $hash = sha1($nstr . $name);
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 5
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function is_valid($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

    /**
     * @param $uuid
     * @return string
     */
    public static function encode($uuid)
    {
        return gmp_base_convert(str_replace('-', '', $uuid), 16, 62);
    }

    /**
     * @param $hashid
     * @return mixed
     */
    public static function decode($hashid)
    {
        return array_reduce([20, 16, 12, 8], function ($uuid, $offset) {
            return substr_replace($uuid, '-', $offset, 0);
        }, str_pad(gmp_base_convert($hashid, 62, 16), 32, '0', STR_PAD_LEFT));
    }
}



