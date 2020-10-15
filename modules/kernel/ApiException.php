<?php

namespace Quantum;

/**
 * Class ApiException
 * @package Quantum
 */
class ApiException {


    /**
     * @var
     */
    public $smarty;
    /**
     * @var
     */
    public $view;

    /**
     * ApiException constructor.
     */
    function __construct() {

    }

    /**
     *
     */
    public static function invalidRequest() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "invalid_request",
            "error_description" => "Invalid request, check the parameters provided",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);



    }

    /**
     *
     */
    public static function  invalidSignedRequest() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "invalid_request",
            "error_description" => "Invalid signed request, check the parameters provided",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  accessDenied() {

        ApiOutput::send_json_headers();
        $error = array(
            "error" => "access_denied",
            "error_description" => "You need a valid access token to access this resource",
            "error_code" => "401 Unauthorized",
        );
        self::toOutput($error);


    }


    /**
     * @param string $code
     */
    public static function  resourceNotFound($code = "resource_not_found")
    {

        http_response_code(404);

        header('HTTP/1.0 404 Not Found');
        $error = array(
            "error" => $code,
            "error_description" => "Requested resource was not found",
            "error_code" => "404 Resource not found"
        );
        self::toOutput($error);

    }

    /**
     *
     */
    public static function  applicationNotFound() {

        header('WWW-Authenticate: OAuth realm="http://clubhub.host"');
        header('HTTP/1.0 401 Unauthorized');
        $error = array(
            "error" => "access_denied",
            "error_description" => "Application not found, or disabled",
            "error_code" => "401 Unauthorized",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  tokenNotFound() {

        header('WWW-Authenticate: OAuth realm="http://api.flightbackpack.com"');
        header('HTTP/1.0 401 Unauthorized');
        $error = array(
            "error" => "access_denied",
            "error_description" => "Token not found, it never existed, or it expired.",
            "error_code" => "401 Unauthorized",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  applicationExceededDailyQuota() {

        header('WWW-Authenticate: OAuth realm="http://api.flightbackpack.com"');
        header('HTTP/1.0 401 Unauthorized');
        $error = array(
            "error" => "access_denied",
            "error_description" => "Application has exceeded the daily quota.",
            "error_code" => "401 Unauthorized",
        );
        self::toOutput($error);



    }

    /**
     *
     */
    public static function  invalidParameters() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "invalid_parameters",
            "error_description" => "Invalid parameters where provided.",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);



    }

    /**
     *
     */
    public static function  invalidRedirectUrl() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "invalid_redirect_uri",
            "error_description" => "The redirect_uri doesn't match this access token redirect_uri",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  domainsDontMatch() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "domains_dont_match",
            "error_description" => "The provided redirect_uri doesn't match this application registered domain",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  iAmATeapot() {

        header('HTTP/1.0 418 I am a teapot');
        $error = array(
            "error" => "i am a tepot",
            "error_code" => "418 I'm a teapot",
        );
        self::toOutput($error);


    }

    /**
     * @param $error
     * @param $code
     * @param $description
     */
    public static function  custom($error, $code, $description) {

        header("HTTP/1.0 $code");
        $error = array(
            "error" => "$error",
            "error_description" => "$description",
            "error_code" => "$code",
        );
        self::toOutput($error);


    }

    /**
     *
     */
    public static function  rateLimitReached($count = null) {

        header('HTTP/1.0 429 Too Many Requests');
        $error = array(
            "error" => "rate_limit",
            "error_description" => "Rate limit for resource reached",
            "error_code" => "429 Too Many Requests",
        );

        if ($count)
            $error['error_description'] .= ' - Requests('.$count.')';

        self::toOutput($error);


    }

    /**
     *
     */
    public static function  sessionRateLimitReached() {

        header('HTTP/1.0 429 Too Many Requests');
        $error = array(
            "error" => "session_rate_limit",
            "error_description" => "Session Rate limit for resource reached",
            "error_code" => "429 Too Many Requests",
        );
        self::toOutput($error);

    }

    /**
     *
     */
    public static function  invalidCSRF() {

        header('HTTP/1.0 400 Bad Request');
        $error = array(
            "error" => "invalid_csrf",
            "error_description" => "Invalid parameters where provided.",
            "error_code" => "400 Bad Request",
        );
        self::toOutput($error);



    }

    /**
     * @param $error
     */
    public static function toOutput($error)
    {
        \ExternalErrorLoggerService::error($error);

        ApiOutput::adaptableOutput($error);
    }



}