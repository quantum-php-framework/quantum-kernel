<?php



namespace Quantum;

/**
 * Class HelperLoader
 * @package Quantum
 */
class HelperLoader
{
    /**
     * HelperLoader constructor.
     */
    function __construct()  {}

    /**
     * @param string $type
     * @return bool
     */
    public static function loadHelpers($type = "all")
    {
        $ipt = InternalPathResolver::getInstance();

        if ($type === 'all')
        {
            $files = scandir($ipt->system_helpers_root);
            //var_dump($files);
            foreach ($files as $file)
            {
                if (Utilities::getExtension($file) == 'php')
                {
                    include_once($ipt->system_helpers_root . $file);
                }

            }
            return true;

        }
        else
            {
            self::loadHelper($type);
        }


    }

    /**
     * @param $filenameWithoutExtension
     * @return bool
     */
    public static function loadHelper($filenameWithoutExtension)
    {
        include_once(InternalPathResolver::getInstance()->system_helpers_root . $filenameWithoutExtension.".php");
        return true;
    }


}