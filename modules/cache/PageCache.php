<?php

namespace Quantum;

use Quantum\Cache\Optimizer\Html;

/**
 * Class PageCache
 * @package Quantum
 */
class PageCache
{
    /**
     * @param $route_uri
     * @param $content
     * @param $expiration
     * @return array
     */
    public static function store($route_uri, $content, $expiration, $tags = null)
    {
        if (self::shouldOptimizeHtml())
            $content = self::optimizeHtml($content);

        $content_date = header_timestamp();

        $data = array();
        $data['content'] = $content;
        $data['content_date'] = $content_date;
        $data['content_expiration'] = gmdate("D, d M Y H:i:s", strtotime($content_date) + $expiration). ' GMT';
        $data['headers'] = headers_list();

        $cache_key = self::getCacheKeyForUri($route_uri);
        Cache::set($cache_key, $data, $expiration);

        return $data;
    }

    /**
     * @param $route_uri
     * @return mixed
     */
    public static function hasContent($route_uri)
    {
        return Cache::has(self::getCacheKeyForUri($route_uri));
    }

    /**
     * @param $route_uri
     * @return mixed
     */
    public static function getCachedHeadersAndContent($route_uri)
    {
        $data = Cache::get(self::getCacheKeyForUri($route_uri), null);

        return $data;
    }

    /**
     * @param $route_uri
     * @return mixed|null
     */
    public static function getContent($route_uri)
    {
        $data = self::getCachedHeadersAndContent($route_uri);

        if ($data)
            return $data['content'];

        return null;
    }

    /**
     * @param $route_uri
     * @return mixed|null
     */
    public static function getHeaders($route_uri)
    {
        $data = self::getCachedHeadersAndContent($route_uri);

        if ($data)
            return $data['headers'];

        return null;
    }

    /**
     * @param $route_uri
     * @param $content
     * @param int $expiration
     */
    public static function append($route_uri, $content, $expiration = 3600)
    {
        $current_contents = self::getContent($route_uri);
        $current_contents .= $content;
        self::store($route_uri, $current_contents, $expiration);
    }

    /**
     *
     */
    public static function flush()
    {
        Cache::flush();
    }

    /**
     * @param $route_uri
     */
    public static function flushUriCache($route_uri)
    {
        Cache::delete(self::getCacheKeyForUri($route_uri));
    }

    /**
     * @param $route_uri
     */
    public static function flushUriCacheIfNeeded($route_uri)
    {
        if (self::hasContent($route_uri))
            self::flushUriCache($route_uri);
    }

    /**
     * @param $route
     * @return bool
     */
    public static function isRouteCachable($route)
    {
        if (\Auth::isUserSessionOpen() && !self::shouldCacheLoggedInUsers($route))
            return false;

        $app_cache_enabled =  get_overridable_app_setting('full_page_cache', false);
        $cache_all_routes  =  get_overridable_app_setting('full_page_cache_all_routes', false);
        if ($app_cache_enabled)
        {
            if ($route->has('page_cache')) {
                return $route->hasEqualParam('page_cache', 1);
            }

            return $cache_all_routes;
        }

        return false;
    }

    private static function isRequestMethodValid()
    {
        $request = Request::getInstance();

        if ($request->isGet() || $request->isHead())
        {
            return true;
        }

        return false;
    }

    /**
     * @param $route
     * @return bool
     */
    public static function shouldCacheRouteRequest($route)
    {
        if (\Auth::isUserSessionOpen())
        {
            if (self::shouldCacheLoggedInUsers($route))
                return self::isRequestMethodValid();

            return false;
        }

        return self::isRequestMethodValid();
    }


    /**
     * @param $route
     * @return mixed
     */
    public static function getKeyForRoute($route)
    {
        $request = Request::getInstance();

        $cache_rule = self::getCacheRule($route);

        if ($cache_rule === 'ignore_query_string')
            $uri = $request->getUri();
        elseif ($cache_rule === 'cache_everything')
            $uri = $request->getUriWithQueryString();

        return $uri;
    }

    /**
     * @param $route
     * @return mixed
     */
    private static function getCacheRule($route)
    {
        $app_config = \QM::config()->getHostedAppConfig();

        if (isset($app_config['full_page_cache_rule']))
            return $app_config['full_page_cache_rule'];

        return $route->get('page_cache_rule', 'cache_everything');
    }

    /**
     * @param $route
     * @return mixed
     */
    public static function getRouteExpiration($route)
    {
        $expiration = get_overridable_app_setting('full_page_cache_expiration', false);

        if ($expiration)
            return $expiration;

        return $route->get('page_cache_expiration', 3600);
    }

    public static function shouldCacheLoggedInUsers($route)
    {
        $result = get_overridable_app_setting('full_page_cache_logged_in_users', false);

        if ($result)
            return $result;

        return $route->get('page_cache_logged_in_users', 0) == 1;
    }


    public static function shouldOptimizeHtml()
    {
        $result = get_overridable_app_setting('full_page_cache_optimize_html', false);

        if ($result)
            return $result;

        $route = \QM::config()->getCurrentRoute();

        if ($route)
            return $route->get('page_cache_optimize_html', 1) == 1;

        return true;
    }


    /**
     * @param $content
     * @return string
     */
    private static function optimizeHtml($content)
    {
        if (qs($content)->isHtml())
        {
            $content = Html::minify($content);
        }

        return $content;
    }

    /**
     * @param $uri
     * @return string
     */
    private static function getCacheKeyForUri($uri)
    {
        $app_config = \QM::config()->getHostedAppConfig();
        $app_uri = $app_config['uri'];

        $key  = 'CK-';
        $key .= $app_uri.$uri;

        $user = \Auth::getUserFromSession();
        if (!empty($user)) {
            $key .= '_'.$user->uuid;
        }

        return $key;
    }

    private static function clearUsersCacheForUri($uri)
    {
        $app_config = \QM::config()->getHostedAppConfig();
        $app_uri = $app_config['uri'];

        $key  = 'CK-';
        $key .= $app_uri.$uri;

        $users = \User::all();

        foreach ($users as $user)
        {
            $user_key = $key .= '_'.$user->uuid;

            if (Cache::has($user_key)) {
                Cache::delete($user_key);
            }
        }
    }
}