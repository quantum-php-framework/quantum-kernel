<?php



namespace Quantum;

/**
 *
 */
define("PARANOID", 1);
/**
 *
 */
define("SQL", 2);
/**
 *
 */
define("SYSTEM", 4);
/**
 *
 */
define("HTML", 8);
/**
 *
 */
define("INT", 16);
/**
 *
 */
define("FLOAT", 32);
/**
 *
 */
define("LDAP", 64);
/**
 *
 */
define("UTF8", 128);


/**
 * Class Security
 * @package Quantum
 */
class Security {


    /**
     * Security constructor.
     */
    function __construct() {
   
    }


    // addslashes wrapper to check for gpc_magic_quotes - gz

    /**
     * @param $string
     * @return string
     */
    public static function nice_addslashes($string)
    {
        // if magic quotes is on the string is already quoted, just return it
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
            return $string;
        else
            return addslashes($string);
    }

    // internal function for utf8 decoding
// thanks to Hokkaido for noticing that PHP's utf8_decode public static function is a little
// screwy, and to jamie for the code
    /**
     * @param $string
     * @return string
     */
    public static function my_utf8_decode($string)
    {
        return strtr($string,
            "???????¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
            "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
    }

    // paranoid sanitization -- only let the alphanumeric set through

    /**
     * @param $string
     * @param string $min
     * @param string $max
     * @return bool|string|string[]|null
     */
    public static function sanitize_paranoid_string($string, $min='', $max='')
    {
        $string = preg_replace("/[^a-zA-Z0-9]/", "", $string);
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
            return FALSE;
        return $string;
    }


//sanitize a string in prep for passing a single argument to system() (or similar)

    /**
     * @param $string
     * @param string $min
     * @param string $max
     * @return bool|string|string[]|null
     */
    public static function sanitize_system_string($string, $min='', $max='')
    {
        $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
        // seperate commands, nested execution, file redirection,
        // background processing, special commands (backspace, etc.), quotes
        // newlines, or some other special characters
        $string = preg_replace($pattern, '', $string);
        $string = '"'.preg_replace('/\$/', '\\\$', $string).'"'; //make sure this is only interpretted as ONE argument
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
            return FALSE;
        return $string;
    }


// sanitize a string for SQL input (simple slash out quotes and slashes)

    /**
     * @param $string
     * @param string $min
     * @param string $max
     * @return bool|string|string[]|null
     */
    public static function sanitize_sql_string($string, $min='', $max='')
    {
        $string = self::nice_addslashes($string); //gz
        $pattern = "/;/"; // jp
        $replacement = "";
        $string = qs($string)->removeCharacters('[]()||<>');
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
            return FALSE;
        return preg_replace($pattern, $replacement, $string);
    }

// sanitize a string for SQL input (simple slash out quotes and slashes)

    /**
     * @param $string
     * @param string $min
     * @param string $max
     * @return bool|string|string[]|null
     */
    public static function sanitize_ldap_string($string, $min='', $max='')
    {
        $pattern = '/(\)|\(|\||&)/';
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
            return FALSE;
        return preg_replace($pattern, '', $string);
    }


// sanitize a string for HTML (make sure nothing gets interpretted!)

    /**
     * @param $string
     * @return string|string[]|null
     */
    public static function sanitize_html_string($string)
    {
        $pattern[0] = '/\&/';
        $pattern[1] = '/</';
        $pattern[2] = "/>/";
        $pattern[3] = '/\n/';
        $pattern[4] = '/"/';
        $pattern[5] = "/'/";
        $pattern[6] = "/%/";
        $pattern[7] = '/\(/';
        $pattern[8] = '/\)/';
        $pattern[9] = '/\+/';
        $pattern[10] = '/-/';
        $replacement[0] = '&amp;';
        $replacement[1] = '&lt;';
        $replacement[2] = '&gt;';
        $replacement[3] = '<br>';
        $replacement[4] = '&quot;';
        $replacement[5] = '&#39;';
        $replacement[6] = '&#37;';
        $replacement[7] = '&#40;';
        $replacement[8] = '&#41;';
        $replacement[9] = '&#43;';
        $replacement[10] = '&#45;';
        return preg_replace($pattern, $replacement, $string);
    }

// make int int!

    /**
     * @param $integer
     * @param string $min
     * @param string $max
     * @return bool|int
     */
    public static function sanitize_int($integer, $min='', $max='')
    {
        $int = intval($integer);
        if((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
            return FALSE;
        return $int;
    }

// make float float!

    /**
     * @param $float
     * @param string $min
     * @param string $max
     * @return bool|float
     */
    public static function sanitize_float($float, $min='', $max='')
    {
        $float = floatval($float);
        if((($min != '') && ($float < $min)) || (($max != '') && ($float > $max)))
            return FALSE;
        return $float;
    }


// glue together all the other public static functions

    /**
     * @param $input
     * @param $flags
     * @param string $min
     * @param string $max
     * @return bool|float|int|string|string[]|null
     */
    public static function sanitize($input, $flags, $min='', $max='')
    {
        if($flags & UTF8) $input = self::my_utf8_decode($input);
        if($flags & PARANOID) $input = self::sanitize_paranoid_string($input, $min, $max);
        if($flags & INT) $input = self::sanitize_int($input, $min, $max);
        if($flags & FLOAT) $input = self::sanitize_float($input, $min, $max);
        if($flags & HTML) $input = self::sanitize_html_string($input);
        if($flags & SQL) $input = self::sanitize_sql_string($input, $min, $max);
        if($flags & LDAP) $input = self::sanitize_ldap_string($input, $min, $max);
        if($flags & SYSTEM) $input = self::sanitize_system_string($input, $min, $max);
        return $input;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_paranoid_string($input, $min='', $max='')
    {
        if($input != self::sanitize_paranoid_string($input, $min, $max))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_int($input, $min='', $max='')
    {
        if($input != self::sanitize_int($input, $min, $max))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_float($input, $min='', $max='')
    {
        if($input != self::sanitize_float($input, $min, $max))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @return bool
     */
    public static function check_html_string($input)
    {
        if($input != self::sanitize_html_string($input))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_sql_string($input, $min='', $max='')
    {
        if($input != self::sanitize_sql_string($input, $min, $max))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_ldap_string($input, $min='', $max='')
    {
        if($input != self::sanitize_ldap_string($input, $min, $max))
            return FALSE;
        return TRUE;
    }

    /**
     * @param $input
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check_system_string($input, $min='', $max='')
    {
        if($input != self::sanitize_system_string($input, $min, $max))
            return FALSE;
        return TRUE;
    }

// glue together all the other public static functions

    /**
     * @param $input
     * @param $flags
     * @param string $min
     * @param string $max
     * @return bool
     */
    public static function check($input, $flags, $min='', $max='')
    {
        $oldput = $input;
        if($flags & UTF8) $input = self::my_utf8_decode($input);
        if($flags & PARANOID) $input = self::sanitize_paranoid_string($input, $min, $max);
        if($flags & INT) $input = self::sanitize_int($input, $min, $max);
        if($flags & FLOAT) $input = self::sanitize_float($input, $min, $max);
        if($flags & HTML) $input = self::sanitize_html_string($input);
        if($flags & SQL) $input = self::sanitize_sql_string($input, $min, $max);
        if($flags & LDAP) $input = self::sanitize_ldap_string($input, $min, $max);
        if($flags & SYSTEM) $input = self::sanitize_system_string($input, $min, $max);
        if($input != $oldput)
            return FALSE;
        return TRUE;
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function sanitizeTaskString($string)
    {
        $t = str_replace("__", "", $string);

        return $t;
    }

    /**
     * @param $data
     * @return array|string|null
     */
    public static function trim($data)
    {
        if ($data == null)
            return null;

        if (is_array($data))
            return array_map('Quantum\Security::trim', $data);
        else
            return is_string($data) ? trim($data) : $data;
    }

    
    
    
}