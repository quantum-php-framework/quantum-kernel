<?php



namespace Quantum;

require_once("Singleton.php");


/**
 * Class Session
 * @package Quantum
 */
class Session extends Singleton
{

    /**
     * Session constructor.
     */
    function __construct()
    {

    }


    /**
     *
     */
    public static function reset()
    {
        self::getInstance()->destroy();
        self::getInstance()->start();
    }

    /**
     *
     */
    public static function start()
    {
        if (self::getInstance()->hasStarted())
            return;

        @session_start();

        //self::set('start_time', date('Y-m-d H:i:s'));
    }

    /**
     *
     */
    public static function destroy()
    {
        if (!self::getInstance()->hasStarted())
            self::getInstance()->start();

        $_SESSION = array();

        session_destroy();
    }

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        if (!self::getInstance()->hasStarted())
            self::getInstance()->start();

        $_SESSION[$key] = $value;
    }


    /**
     * @param $key
     * @param bool $fallback
     * @return bool|mixed
     */
    public static function get($key, $fallback = false)
    {
        if (!self::getInstance()->hasStarted())
            self::getInstance()->start();

        if (self::hasParam($key))
            return $_SESSION[$key];

        return $fallback;
    }


    /**
     * @param $key
     */
    public static function remove ($key)
    {
        if (is_array($_SESSION) && array_key_exists($key, $_SESSION))
        {
            unset($_SESSION[$key]);
        }
    }


    /**
     * @return bool
     */
    public static function hasStarted()
    {
        if (function_exists("session_status"))
            return session_status() === PHP_SESSION_ACTIVE ? true : false;

        if (session_id() != '')
            return true;

        return false;

    }

    /**
     * @param $key
     * @return bool
     */
    public static function hasParam($key)
    {
        if (!self::getInstance()->hasStarted())
            self::getInstance()->start();

        if (isset($_SESSION) && is_array($_SESSION))
            return array_key_exists($key, $_SESSION);

        return false;

    }

    /**
     * @param $key
     * @return bool
     */
    public static function isMissingParam($key)
    {
        if (self::getInstance()->hasParam($key))
            return false;

        return true;
    }

    /**
     * @param $key
     */
    public static function increaseCounter($key)
    {
        $s = self::getInstance();

        if ($s->isMissingParam($key))
            $s->set($key, 0);

        $current = $s->get($key);

        $inc = $current + 1;

        $s->set($key, $inc);
    }

    /**
     * @return mixed
     */
    public static function getData()
    {
        return $_SESSION;
    }

    /**
     * @return string
     */
    public static function getId()
    {
        if (!self::getInstance()->hasStarted())
            self::getInstance()->start();

        return session_id();
    }


   


    
    
    
}