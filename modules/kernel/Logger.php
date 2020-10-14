<?php

namespace Quantum;

use Monolog;

/**
 * Class Logger
 * @package Quantum
 */
class Logger extends Singleton
{
    /**
     * @return Monolog\Logger
     */
    public $monolog;

    /**
     * Logger constructor.
     */
    function __construct()
    {

    }

    /**
     * @param $filename
     * @param $level
     * @param string $channel_name
     * @param bool $pushStreamHandler
     * @return Monolog\Logger
     * @throws \Exception
     */
    public static function createMonolog($filename, $level, $channel_name = "quantum", $pushStreamHandler = true)
    {
        $monolog = new Monolog\Logger($channel_name);

        if ($pushStreamHandler)
            $monolog->pushHandler(new Monolog\Handler\StreamHandler(InternalPathResolver::getInstance()->logs_root.$filename.".log", $level));

        return $monolog;
    }


    /**
     * @param $message
     * @throws \Exception
     */
    public static function dev($message)
    {
        $monolog = self::createMonolog('dev', Monolog\Logger::DEBUG);
        $monolog->info(self::getCaller()." : ".$message);
    }


    /**
     * @param $message
     * @throws \Exception
     */
    public static function error($message)
    {
        $monolog = self::createMonolog('errors', Monolog\Logger::ERROR);
        $monolog->error(self::getCaller()." : ".$message);

        Mailer::notifyCreator("Error occurred at cStore!", $message);
    }


    /**
     * @param $message
     * @throws \Exception
     */
    public static function info($message)
    {
        $monolog = self::createMonolog('info', Monolog\Logger::INFO);
        $monolog->info(self::getCaller()." : ".$message);
    }


    /**
     * @param $message
     * @throws \Exception
     */
    public static function security($message)
    {
        $monolog = self::createMonolog('security', Monolog\Logger::WARNING);
        $monolog->warning(self::getCaller()." : ".$message);
    }


    /**
     * @param $message
     * @param $filename
     * @param int $level
     * @throws \Exception
     */
    public static function custom($message, $filename, $level = Monolog\Logger::DEBUG)
    {
        $monolog = self::createMonolog($filename, $level);

        $monolog->info(self::getCaller()." : ".$message);
    }


    /**
     * @param $message
     * @param $filename
     * @throws \Exception
     */
    public static function addMessage($message, $filename)
    {
        self::custom($message, $filename);
    }


    /**
     * @return string
     */
    static function getCaller()
    {
        $e = new \Exception();
        $trace = $e->getTrace();

        $i = 4;

        if (isset($trace[$i]))
        {
            $datum = $trace[$i];

            $t = "";

            if (isset($datum['class']))
            {
                $t .= $datum['class'];
                $t .= '::';
            }

            $t .= $datum['function'];

            if (isset($datum['line']))
            {
                $t .= ' ('.$datum['line'].')';
            }

            return $t;
        }


    }

    
}