<?php



namespace Quantum;

/**
 * Class Exception
 * @package Quantum
 */
class Exception
{

    /**
     * Exception constructor.
     */
    function __construct() {

    }


    /**
     * @param $var
     */
    static function throw404IfEmpty($var)
    {
        if (empty($var))
        {
           ApiException::resourceNotFound();
        }
    }

    /**
     * @param $var
     */
    static function throwInvalidParametersIfEmpty($var)
    {
        if (empty($var))
        {
            ApiException::invalidParameters();
        }
    }

    /**
     * @param $var
     */
    static function throwResourceNotFoundIfEmpty($var)
    {
        if (empty($var))
        {
            ApiException::resourceNotFound();
        }
    }

    /**
     * @param $http_code
     * @param string $error_name
     * @param string $error_description
     */
    function throwCustomApiException($http_code, $error_name = "", $error_description = "")
    {
        $error_name = ($error_name == "" ? "unnamed_error" : $error_name);
        $error_description = ($error_description == "" ? (string) $http_code : $error_description);

        ApiException::custom($http_code, $error_name, $error_description);
    }

    /**
     * @param int $statusCode
     * @param null $statusPhrase
     * @param array $headers
     * @throws HttpException
     */
    function throwHttpException($statusCode = 500, $statusPhrase = null, array $headers = array())
    {
        throw new HttpException($statusCode);
    }
    
    
    
}