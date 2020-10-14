<?php

namespace Quantum;


/**
 * Class Utilities
 * @package Quantum
 */
class Utilities {


    /**
     * Utilities constructor.
     */
    function __construct() {

    }

    /**
     * @return string
     */
    public static function guid() {

        return uniqid();
    }

    /**
     * @param $url
     * @return bool
     */
    public static function validateUrl($url) {
	    
	    if (!preg_match('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', $url)) {
		    return false;
		    }
		    return true;
    }


    /**
     * @param $str
     * @return bool|string
     */
    public static  function getExtension($str) {
	  $i = strrpos($str,".");
	  if (!$i) { return false; } 
	  $l = strlen($str) - $i;
	  $ext = substr($str,$i+1,$l);
	  return $ext;
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 
    
	// Uncomment one of the following alternatives
	// $bytes /= pow(1024, $pow);
	 $bytes /= (1 << (10 * $pow)); 
    
	return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    /**
     * @param $string
     * @return string
     */
    public static function genHash($string) {
		
	$secret = hash('ripemd160', base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), microtime(true), uniqid(mt_rand(), true)).$string));
	
	return $secret;
	
    }

    /**
     * @param $url
     */
    public static function redirect($url)
    {
	    header("Location:$url");
	    exit();
    }

    /**
     * @param $input
     * @return string
     */
    public static function base64_url_encode($input) {
	    return strtr(base64_encode($input), '+/=', '-_,');
    }

    /**
     * @param $input
     * @return bool|string
     */
    public static function base64_url_decode($input) {
       return base64_decode(strtr($input, '-_,', '+/='));
    }

    /**
     * @param $email
     * @return mixed
     */
    public static function validateEmail($email ){
	    return filter_var( $email, FILTER_VALIDATE_EMAIL );
    }

    /**
     * @param $string
     * @param $test_string
     * @return bool
     */
    public static function stringContainsIgnoreCase ($string, $test_string)
	{
		if (stripos($string, $test_string) !== FALSE)
			return true;

		return false;

	}

    /**
     * @return QString
     */
    public static function genUUID_V4()
    {
        return Uuid::v4();
    }

    /**
     * @param $domain
     * @param bool $debug
     * @return mixed|string
     */
    public static function getDomainFromUrl($domain, $debug = false)
    {
        $domain = parse_url($domain, PHP_URL_HOST);

        $original = $domain = strtolower($domain);
        if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }
        $debug ? print('<strong style="color:green">&raquo;</strong> Parsing: '.$original) : false;
        $arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
            return $value !== 'www';
        }), 0); //rebuild array indexes
        if (count($arr) > 2)
        {
            $count = count($arr);
            $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
            $debug ? print(" (parts count: {$count})") : false;
            if (count($_sub) === 2) // two level TLD
            {
                $removed = array_shift($arr);
                if ($count === 4) // got a subdomain acting as a domain
                {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            }
            elseif (count($_sub) === 1) // one level TLD
            {
                $removed = array_shift($arr); //remove the subdomain
                if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
                {
                    array_unshift($arr, $removed);
                }
                else
                {
                    // non country TLD according to IANA
                    $tlds = array(
                        'aero',
                        'arpa',
                        'asia',
                        'biz',
                        'cat',
                        'com',
                        'coop',
                        'edu',
                        'gov',
                        'info',
                        'jobs',
                        'mil',
                        'mobi',
                        'museum',
                        'name',
                        'net',
                        'org',
                        'post',
                        'pro',
                        'tel',
                        'travel',
                        'xxx',
                    );
                    if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
                    {
                        array_shift($arr);
                    }
                }
                $debug ? print("<br>\n" .'[*] One level TLD: <strong>'.join('.', $_sub).'</strong> ') : false;
            }
            else // more than 3 levels, something is wrong
            {
                for ($i = count($_sub); $i > 1; $i--)
                {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            }
        }
        elseif (count($arr) === 2)
        {
            $arr0 = array_shift($arr);
            if (strpos(join('.', $arr), '.') === false
                && in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
            {
                $debug ? print("<br>\n" .'Seems invalid domain: <strong>'.join('.', $arr).'</strong> re-adding: <strong>'.$arr0.'</strong> ') : false;
                // seems invalid domain, restore it
                array_unshift($arr, $arr0);
            }
        }
        $debug ? print("<br>\n".'<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">'. join('.', $arr) ."</span><br>\n") : false;
        return join('.', $arr);
    }

    /**
     * @return string|string[]|null
     */
    public static function getUriPath()
    {
        $uri = urldecode(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );

        $uri = preg_replace('/[0-9]+/', '', $uri);



        return $uri;
    }


    /**
     * @param $s
     * @param bool $use_forwarded_host
     * @return string
     */
    public static function getUrlOrigin($s, $use_forwarded_host = false )
    {
        $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
        $sp       = strtolower( $s['SERVER_PROTOCOL'] );
        $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
        $port     = $s['SERVER_PORT'];
        $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
        $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
        $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    /**
     * @param bool $use_forwarded_host
     * @return string
     */
    public static function getFullUrl($use_forwarded_host = false )
    {
        return self::getUrlOrigin( $_SERVER, $use_forwarded_host ) . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return false|string
     */
    public static function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @return mixed
     */
    public static function getDomain()
    {
        return $_SERVER['SERVER_NAME'];
    }



    
}