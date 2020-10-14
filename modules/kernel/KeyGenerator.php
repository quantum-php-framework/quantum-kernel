<?php



namespace Quantum ;

//use Closure;


/**
 * Class KeyGenerator
 * @package Quantum
 */
class KeyGenerator{


    /**
     * KeyGenerator constructor.
     */
    function __construct() {
     
    }


    /**
     * @param $length
     * @return string
     */
    static function alphaNumeric($length)
    {
        $key = '';
        $chars = array_merge(range(0, 9), range('a', 'z'), range(0, 9), range('A', 'Z'), range(0, 9));
        shuffle($chars);
        $chars = str_shuffle(str_rot13(join('', $chars)));
        $split = ceil($length / 5);
        $size = strlen($chars);
        $splitSize = ceil($size / $split);
        $chunkSize = $splitSize + mt_rand(1, 5);
        $chunkArray = array();
        while ($split != 0) {
            $strip = substr($chars, mt_rand(0, $size - $chunkSize), $chunkSize);
            array_push($chunkArray, strrev($strip));
            $split--;
        }
        foreach ($chunkArray as $set) {
            $adjust = ((($length - strlen($key)) % 5) == 0) ? 5 : ($length - strlen($key)) % 5;
            $setSize = strlen($set);
            $key .= substr($set, mt_rand(0, $setSize - $adjust), $adjust);
        }

        return str_rot13(str_shuffle($key));
    }

    /**
     * @param $length
     * @return string
     */
    static function numeric($length)
    {
        $chars = str_shuffle('3759402687094152031368921');
        $chars = str_shuffle(str_repeat($chars, ceil($length / strlen($chars))));

        return strrev(str_shuffle(substr($chars, mt_rand(0, (strlen($chars) - $length - 1)), $length)));
    }

    /**
     * @param $length
     * @return bool|string
     */
    static function string($length)
    {
        $token = '';
        $tokenlength = round($length * 3 / 4);
        for ($i = 0; $i < $tokenlength; ++$i) {
            $token .= chr(rand(32,1024));
        }
        $token = base64_encode(str_shuffle($token));
        return substr($token, -$length);
    }

    
    
}