<?php

namespace Quantum\Http;

/**
 * Class HeaderFactory
 * @package Quantum\Http
 */
class HeaderFactory
{
    /**
     * @param $content
     */
    public static function setContentTypeHeader($content)
    {
        if (!is_string($content))
            return;

        if (qs($content)->isJson())
        {
            header("Content-Type: application/json");
        }
        elseif (qs($content)->isHtml())
        {
            header("Content-Type: text/html");
        }
        elseif (qs($content)->isXml())
        {
            header("Content-Type: application/xml");
        }
    }

    /**
     * @param $hitOrMiss
     */
    public static function setCacheHitHeader($hitOrMiss)
    {
        set_header('X-QCache', $hitOrMiss);
    }


    /**
     * @param $date
     */
    public static function setCacheContentDateHeader($date)
    {
        set_header('X-QCache-Date', $date);
    }

    /**
     * @param $date
     */
    public static function setLastModifiedHeader($date)
    {
        set_header('Last-Modified', $date);
    }

    public static function setCacheControlHeader($expiration_date = null)
    {
        $cache_privacy = \Auth::isUserSessionOpen() ? 'private' : 'public';

        $max_age =  !empty($expiration_date) ? ', max-age='. (strtotime($expiration_date) - time()) : '';

        set_header('Cache-Control', $cache_privacy.', no-cache, must-revalidate'.$max_age);
    }

    public static function setExpiresHeader($date)
    {
        set_header('Expires', $date);
    }
}