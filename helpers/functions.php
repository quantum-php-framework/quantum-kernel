<?php


/**
 * Helpers are just that... short methods
 * for calling Quantum\Methods
 */


if (!function_exists('before_filter'))
{
    /**
     * @param $filter_name
     * @param null $params
     * @return bool
     */
    function before_filter($filter_name, $params = null)
    {
        if (!empty($filter_name))
        {
            if (Quantum\Filters::runBeforeFilter((string)$filter_name, $params))
                return true;
        }

        return false;
    }

}

if (!function_exists('qm_trigger_logout'))
{
    /**
     * @param null $uri
     */
    function qm_trigger_logout($uri = null)
    {

        \Auth::logout($uri);
    }

}

if (!function_exists('datestamp'))
{
    /**
     * @return false|string
     */
    function datestamp()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('datetime_to_utc'))
{
    /**
     * @param $date
     * @return string
     * @throws Exception
     */
    function datetime_to_utc($date)
    {
        $d = new DateTime($date);
        $date = $d->setTimezone(new DateTimeZone('UTC'));
        return $date->format('Y-m-d H:i:s'). " UTC";
    }
}

if (!function_exists('xxhash'))
{
    /**
     * @param $string
     * @return string
     */
    function xxhash($string)
    {
        $hash = new \Quantum\SlowHash();
        return $hash->hash($string);
    }
}

if (!function_exists('to_nicetime'))
{
    /**
     * @param $date
     * @return string
     */
    function to_nicetime($date)
    {
        if(empty($date)) {
            return "No date provided";
        }

        $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths         = array("60","60","24","7","4.35","12","10");

        $now             = time();
        $unix_date         = strtotime($date);


        // check validity of date
        if(empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        if($now > $unix_date) {
            $difference     = $now - $unix_date;
            $tense         = "ago";

        } else {
            $difference     = $unix_date - $now;
            $tense         = "from now";
        }

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j] {$tense}";
    }
}

if (!function_exists('quuid'))
{
    /**
     * @return \Quantum\QString
     */
    function quuid()
    {
        return Quantum\Utilities::genUUID_V4();
    }
}

if (!function_exists('qcsrf'))
{
    /**
     * @return bool|string
     * @throws Exception
     */
    function qcsrf()
    {
        return Quantum\CSRF::create();
    }
}

if (!function_exists('gmp_base_convert'))
{
    /**
     * @param $value
     * @param $initialBase
     * @param $newBase
     * @return string
     */
    function gmp_base_convert($value, $initialBase, $newBase)
    {
        return gmp_strval(gmp_init($value, $initialBase), $newBase);
    }

}




if (!function_exists('to_datestamp'))
{
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_datestamp($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('Y-m-d H:i:s', $timestamp);
    }
}

if (!function_exists('to_us_date'))
{
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_us_date($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('m-d-Y H:i:s', $timestamp);
    }
}

if (!function_exists('to_world_date'))
{
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_world_date($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('d-m-Y H:i:s', $timestamp);
    }
}

if (!function_exists('base64_url_encode'))
{
    /**
     * @param $data
     * @return string
     */
    function base64_url_encode($data)
    {
        return \Quantum\Utilities::base64_url_encode($data);
    }
}

if (!function_exists('base64_url_decode'))
{
    /**
     * @param $data
     * @return bool|string
     */
    function base64_url_decode($data)
    {
        return \Quantum\Utilities::base64_url_decode($data);
    }
}



if (!function_exists('get_request_param'))
{
    /**
     * @param $key
     * @param string $fallback
     * @return mixed
     */
    function get_request_param($key, $fallback = '')
    {

        return \Quantum\Request::getInstance()->getParam($key, $fallback);
    }
}

if (!function_exists('get_post_param'))
{
    /**
     * @param $key
     * @param $fallback
     * @return mixed
     */
    function get_post_param($key, $fallback)
    {

        return \Quantum\Request::getInstance()->getPostParam($key, $fallback);
    }
}



if (!function_exists('has_request_param'))
{
    /**
     * @param $key
     * @return mixed
     */
    function has_request_param($key)
    {

        return \Quantum\Request::getInstance()->hasRequestParam($key);
    }
}

if (!function_exists('has_post_param'))
{
    /**
     * @param $key
     * @return mixed
     */
    function has_post_param($key)
    {

        return \Quantum\Request::getInstance()->hasPostParam($key);
    }
}

if (!function_exists('redirect_post'))
{

    /**
     * @param $url
     * @param array $data
     * @param array|null $headers
     * @throws Exception
     */
    function redirect_post($url, array $data, array $headers = null)
    {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        if (!is_null($headers)) {
            $params['http']['header'] = '';
            foreach ($headers as $k => $v) {
                $params['http']['header'] .= "$k: $v\n";
            }
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            echo @stream_get_contents($fp);
            die();
        } else {
            // Error
            throw new Exception("Error loading '$url', $php_errormsg");
        }
    }

}


if (!function_exists('redirect_to'))
{
    /**
     * @param $uri
     */
    function redirect_to($uri)
    {
        Quantum\Utilities::redirect($uri);
    }

}

if (!function_exists('pr'))
{
    /**
     * @param $var
     */
    function pr($var)
    {
        var_dump($var);
    }
}

if (!function_exists('teapot'))
{
    /**
     *
     */
    function teapot()
    {
        echo "I am a teapot<br/>";
    }
}

if (!function_exists('new_vt'))
{
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return Quantum\ValueTree
     */

    function new_vt($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('valuetree'))
{
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return Quantum\ValueTree
     */

    function valuetree($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('vt'))
{
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return Quantum\ValueTree
     */

    function vt($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('fallback'))
{
    /**
     * If Empty Fallback to
     *
     * @param  mixed   $test
     * @param  mixed   $fallback
     * return mixed;
     */

    function fallback($test, $fallback)
    {
        if (!empty($test))
            return $test;

        return $fallback;
    }
}

if (!function_exists('halt'))
{
    /**
     * return void;
     */

    function halt()
    {
        throw new \Error('Halted!');
    }
}

if (!function_exists('fuck'))
{
    /**
     * return void;
     */

    function fuck()
    {
        throw new \Error('fuck!');
    }
}

if (!function_exists('yesOrNo'))
{
    /**
     * return void;
     */

    function yesOrNo($value)
    {
        if ($value === true || $value === 1 || $value === '1')
            return "Yes";

        return "No";
    }
}

if (!function_exists('yesOrNothing'))
{
    /**
     * return void;
     */

    function yesOrNothing($value)
    {
        if ($value === true || $value === 1 || $value === '1')
            return "Yes";

        return "";
    }
}





if (!function_exists('wtf'))
{
    /**
     * return void;
     */

    function wtf()
    {
        return "I have no idea!";
    }
}

if (!function_exists('string'))
{
    /**
     * Creates a new Quantum\QString
     *
     * @param  string   $string
     * @return Quantum\QString
     */

    function string($string)
    {
        return Quantum\QString::create($string);
    }
}

if (!function_exists('qs'))
{
    /**
     * Creates a new Quantum\QString
     *
     * @param  string   $string
     * @return Quantum\QString
     */

    function qs($string = '')
    {
        return Quantum\QString::create($string);
    }
}

if (!function_exists('to_qs'))
{
    /**
     * Converts an array string values to Quantum\Strings
     *
     * @param  string   $string
     * @return array
     */

    function to_qs($array)
    {
        return Quantum\QString::convertAllStringsInArray($array);
    }
}

if (!function_exists('qf'))
{
    /**
     * Creates a new Quantum\File
     *
     * @param  string   $filePath
     * @return Quantum\File
     */

    function qf($filePath)
    {
        return Quantum\File::newFile($filePath);
    }
}

if (!function_exists('qurl'))
{
    /**
     * Creates a new Quantum\URL
     *
     * @param  string   $filePath
     * @return Quantum\URL
     */

    function qurl($path)
    {
        return new Quantum\URL($path);
    }
}

if (!function_exists('qform'))
{
    /**
     * Creates a new Quantum\Form
     *
     * @param  string   $filePath
     * @return Quantum\Form
     */

    function qform(Quantum\FormElementsFactory $factory, $action = "", $method = "post", $name = "", $addCSRF = true)
    {
        return new Quantum\Form($factory, $action, $method, $name, $addCSRF);
    }
}

if (!function_exists('new_locked_vt'))
{
    /**
     * Creates a new locked and unmutable Quantum\Valuetree
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return Quantum\ValueTree
     */

    function new_locked_vt($properties = array())
    {
        return new Quantum\ValueTree($properties, true, true);
    }
}

if (!function_exists('is_vt'))
{
    /**
     * Check if object is a valuetree
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return bool
     */

    function is_vt($object)
    {
        return (is_a($object, Quantum\ValueTree::class));
    }
}

if (!function_exists('round_cents'))
{
    /**
     * Round float up and ensures 2 decimals
     *
     * @param  string   $float
     * @return string
     */

    function round_cents($float)
    {
        $num = round($float, 2);

        return number_format($num, 2);
    }
}

if (!function_exists('dumpAndDie'))
{
    /**
     * @param $var
     */
    function dumpAndDie($var)
    {
        pr($var);
        exit();
    }
}

if (!function_exists('humanize'))
{
    /**
     * @param $string
     * @return \Quantum\QString
     */
    function humanize($string)
    {
       return qs($string)->humanize()->toStdString();
    }
}

if (!function_exists('remove_duplicates'))
{
    /**
     * @param $string
     * @return string
     */
    function remove_duplicates($string)
    {
        $a = qs($string)->explode(" ");

        $a = array_unique($a);

        return implode("", $a);
    }
}

if (!function_exists('to_password'))
{
    /**
     * @param $string
     * @param $salt
     * @param string $algo
     * @return string
     */
    function to_password($string, $salt, $algo = 'ripemd160')
    {

        return hash($algo, $string . $salt);

    }
}

if (!function_exists('logger'))
{
    /**
     * @param $message
     */
    function logger($message)
    {
        Quantum\Logger::logError($message);
    }
}

if (!function_exists('devlog'))
{
    /**
     * @param $message
     */
    function devlog($message)
    {
        Quantum\Logger::dev($message);
    }
}

if (!function_exists('throwInvalidParametersIfEmpty'))
{
    /**
     * @param $var
     */
    function throwInvalidParametersIfEmpty($var)
    {
        Quantum\Exception::throwInvalidParametersIfEmpty($var);
    }
}

if (!function_exists('throwResourceNotFoundIfEmpty'))
{
    /**
     * @param $var
     */
    function throwResourceNotFoundIfEmpty($var)
    {
        Quantum\Exception::throwResourceNotFoundIfEmpty($var);
    }
}

if (!function_exists('is_positive'))
{
    /**
     * @param $num
     * @return bool
     */
    function is_positive($num)
    {
        if (is_numeric($num))
        {
            if ($num > 0)
                return true;
        }

        return false;
    }
}

if (!function_exists('is_positive_and_below'))
{
    /**
     * @param $num
     * @param $max
     * @return bool
     */
    function is_positive_and_below($num, $max)
    {
        if (is_positive($num))
        {
            if ($num < $max)
                return true;
        }

        return false;
    }
}

if (!function_exists('qm_profiler_start'))
{
    /**
     * @param $timer_name
     */
    function qm_profiler_start($timer_name)
    {
        Quantum\Profiler::start($timer_name);
    }
}

if (!function_exists('qm_profiler_stop'))
{
    /**
     * @param $timer_name
     */
    function qm_profiler_stop($timer_name)
    {
        Quantum\Profiler::stop($timer_name);
    }
}

if (!function_exists('qm_profiler_enable'))
{
    /**
     *
     */
    function qm_profiler_enable()
    {
        Quantum\Profiler::enable();
    }
}

if (!function_exists('qm_profiler_disable'))
{
    /**
     *
     */
    function qm_profiler_disable()
    {
        Quantum\Profiler::disable();
    }
}

if (!function_exists('qm_profiler_html'))
{
    /**
     *
     */
    function qm_profiler_html()
    {
        echo Quantum\Profiler::toHtml();
    }
}

if (!function_exists('throw_exception'))
{
    /**
     * @param $msg
     * @throws Exception
     */
    function throw_exception($msg)
    {
        throw new Exception($msg);
    }
}

if (!function_exists('is_closure')) {

    /**
     * @param $t
     * @return bool
     */
    function is_closure($t)
    {
        return is_object($t) && ($t instanceof Closure);
    }

}

if (!function_exists('instance_of')) {

    /**
     * @param $obj
     * @param $className
     * @return bool
     */
    function instance_of($obj, $className)
    {
        return is_object($obj) && ($obj instanceof $className);
    }

}


if (!function_exists('is_psr7_response')) {

    /**
     * @param $t
     * @return bool
     */
    function is_psr7_response($t)
    {
        return is_object($t) && ($t instanceof \Quantum\Psr7\Response);
    }

}

if (!function_exists('redirect')) {

    /**
     * @param $t
     * @return \Quantum\Psr7\Response\RedirectResponse
     */
    function redirect($url)
    {
        return \Quantum\Psr7\ResponseFactory::redirect($url);
    }

}

if (!function_exists('response')) {


    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return \Quantum\Psr7\Response\EmptyResponse|\Quantum\Psr7\Response\HtmlResponse|\Quantum\Psr7\Response\JsonResponse|\Quantum\Psr7\Response\RedirectResponse|\Quantum\Psr7\Response\XmlResponse
     */
    function response($contents = '', $status = 200, array $headers = [])
    {
        return \Quantum\Psr7\ResponseFactory::fromVariableData($contents, $status, $headers);
    }

}


/**
 * Helpers are just that... short methods
 * for calling Quantum\Methods
 */


if (!function_exists('before_filter')) {
    /**
     * @param $filter_name
     * @param null $params
     * @return bool
     */
    function before_filter($filter_name, $params = null)
    {
        if (!empty($filter_name)) {
            if (Quantum\Filters::runBeforeFilter((string)$filter_name, $params))
                return true;
        }

        return false;
    }

}

if (!function_exists('qm_trigger_logout')) {
    /**
     * @param null $uri
     */
    function qm_trigger_logout($uri = null)
    {

        \Auth::logout($uri);
    }

}

if (!function_exists('datestamp')) {
    /**
     * @return false|string
     */
    function datestamp()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('datetime_to_utc')) {
    /**
     * @param $date
     * @return string
     * @throws Exception
     */
    function datetime_to_utc($date)
    {
        $d = new DateTime($date);
        $date = $d->setTimezone(new DateTimeZone('UTC'));
        return $date->format('Y-m-d H:i:s') . " UTC";
    }
}

if (!function_exists('xxhash')) {
    /**
     * @param $string
     * @return string
     */
    function xxhash($string)
    {
        $hash = new \Quantum\SlowHash();
        return $hash->hash($string);
    }
}

if (!function_exists('to_nicetime')) {
    /**
     * @param $date
     * @return string
     */
    function to_nicetime($date)
    {
        if (empty($date)) {
            return "No date provided";
        }

        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);


        // check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";

        } else {
            $difference = $unix_date - $now;
            $tense = "from now";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return "$difference $periods[$j] {$tense}";
    }
}

if (!function_exists('quuid')) {
    /**
     * @return \Quantum\QString
     */
    function quuid()
    {
        return Quantum\Utilities::genUUID_V4();
    }
}

if (!function_exists('qcsrf')) {
    /**
     * @return bool|string
     * @throws Exception
     */
    function qcsrf()
    {
        return Quantum\CSRF::create();
    }
}

if (!function_exists('gmp_base_convert')) {
    /**
     * @param $value
     * @param $initialBase
     * @param $newBase
     * @return string
     */
    function gmp_base_convert($value, $initialBase, $newBase)
    {
        return gmp_strval(gmp_init($value, $initialBase), $newBase);
    }

}


if (!function_exists('to_datestamp')) {
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_datestamp($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('Y-m-d H:i:s', $timestamp);
    }
}

if (!function_exists('to_us_date')) {
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_us_date($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('m-d-Y H:i:s', $timestamp);
    }
}

if (!function_exists('to_world_date')) {
    /**
     * @param $timestamp
     * @return false|string
     */
    function to_world_date($timestamp)
    {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        return date('d-m-Y H:i:s', $timestamp);
    }
}

if (!function_exists('base64_url_encode')) {
    /**
     * @param $data
     * @return string
     */
    function base64_url_encode($data)
    {
        return \Quantum\Utilities::base64_url_encode($data);
    }
}

if (!function_exists('base64_url_decode')) {
    /**
     * @param $data
     * @return bool|string
     */
    function base64_url_decode($data)
    {
        return \Quantum\Utilities::base64_url_decode($data);
    }
}


if (!function_exists('get_request_param')) {
    /**
     * @param $key
     * @param string $fallback
     * @return mixed
     */
    function get_request_param($key, $fallback = '')
    {

        return \Quantum\Request::getInstance()->getParam($key, $fallback);
    }
}

if (!function_exists('get_post_param')) {
    /**
     * @param $key
     * @param $fallback
     * @return mixed
     */
    function get_post_param($key, $fallback)
    {

        return \Quantum\Request::getInstance()->getPostParam($key, $fallback);
    }
}


if (!function_exists('has_request_param')) {
    /**
     * @param $key
     * @return mixed
     */
    function has_request_param($key)
    {

        return \Quantum\Request::getInstance()->hasRequestParam($key);
    }
}

if (!function_exists('has_post_param')) {
    /**
     * @param $key
     * @return mixed
     */
    function has_post_param($key)
    {

        return \Quantum\Request::getInstance()->hasPostParam($key);
    }
}

if (!function_exists('redirect_post')) {

    /**
     * @param $url
     * @param array $data
     * @param array|null $headers
     * @throws Exception
     */
    function redirect_post($url, array $data, array $headers = null)
    {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        if (!is_null($headers)) {
            $params['http']['header'] = '';
            foreach ($headers as $k => $v) {
                $params['http']['header'] .= "$k: $v\n";
            }
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            echo @stream_get_contents($fp);
            die();
        } else {
            // Error
            throw new Exception("Error loading '$url', $php_errormsg");
        }
    }

}


if (!function_exists('redirect_to')) {
    /**
     * @param $uri
     */
    function redirect_to($uri)
    {
        Quantum\Utilities::redirect($uri);
    }

}

if (!function_exists('pr')) {
    /**
     * @param $var
     */
    function pr($var)
    {
        var_dump($var);
    }
}

if (!function_exists('teapot')) {
    /**
     *
     */
    function teapot()
    {
        echo "I am a teapot<br/>";
    }
}

if (!function_exists('new_vt')) {
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return Quantum\ValueTree
     */

    function new_vt($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('valuetree')) {
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return Quantum\ValueTree
     */

    function valuetree($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('vt')) {
    /**
     * Creates a new Quantum\Valuetree
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return Quantum\ValueTree
     */

    function vt($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        return new Quantum\ValueTree($properties, $shouldBeLocked, $shouldBeUnmutable);
    }
}

if (!function_exists('fallback')) {
    /**
     * If Empty Fallback to
     *
     * @param mixed $test
     * @param mixed $fallback
     * return mixed;
     */

    function fallback($test, $fallback)
    {
        if (!empty($test))
            return $test;

        return $fallback;
    }
}

if (!function_exists('halt')) {
    /**
     * return void;
     */

    function halt()
    {
        throw new \Error('Halted!');
    }
}

if (!function_exists('fuck')) {
    /**
     * return void;
     */

    function fuck()
    {
        throw new \Error('fuck!');
    }
}

if (!function_exists('yesOrNo')) {
    /**
     * return void;
     */

    function yesOrNo($value)
    {
        if ($value === true || $value === 1 || $value === '1')
            return "Yes";

        return "No";
    }
}

if (!function_exists('yesOrNothing')) {
    /**
     * return void;
     */

    function yesOrNothing($value)
    {
        if ($value === true || $value === 1 || $value === '1')
            return "Yes";

        return "";
    }
}


if (!function_exists('wtf')) {
    /**
     * return void;
     */

    function wtf()
    {
        return "I have no idea!";
    }
}

if (!function_exists('string')) {
    /**
     * Creates a new Quantum\QString
     *
     * @param string $string
     * @return Quantum\QString
     */

    function string($string)
    {
        return Quantum\QString::create($string);
    }
}

if (!function_exists('qs')) {
    /**
     * Creates a new Quantum\QString
     *
     * @param string $string
     * @return Quantum\QString
     */

    function qs($string = '')
    {
        return Quantum\QString::create($string);
    }
}

if (!function_exists('to_qs')) {
    /**
     * Converts an array string values to Quantum\Strings
     *
     * @param string $string
     * @return array
     */

    function to_qs($array)
    {
        return Quantum\QString::convertAllStringsInArray($array);
    }
}

if (!function_exists('qf')) {
    /**
     * Creates a new Quantum\File
     *
     * @param string $filePath
     * @return Quantum\File
     */

    function qf($filePath)
    {
        return Quantum\File::newFile($filePath);
    }
}

if (!function_exists('qform')) {
    /**
     * Creates a new Quantum\Form
     *
     * @param string $filePath
     * @return Quantum\Form
     */

    function qform(Quantum\FormElementsFactory $factory, $action = "", $method = "post", $name = "", $addCSRF = true)
    {
        return new Quantum\Form($factory, $action, $method, $name, $addCSRF);
    }
}

if (!function_exists('new_locked_vt')) {
    /**
     * Creates a new locked and unmutable Quantum\Valuetree
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return Quantum\ValueTree
     */

    function new_locked_vt($properties = array())
    {
        return new Quantum\ValueTree($properties, true, true);
    }
}

if (!function_exists('is_vt')) {
    /**
     * Check if object is a valuetree
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return bool
     */

    function is_vt($object)
    {
        return (is_a($object, Quantum\ValueTree::class));
    }
}

if (!function_exists('round_cents')) {
    /**
     * Round float up and ensures 2 decimals
     *
     * @param string $float
     * @return string
     */

    function round_cents($float)
    {
        $num = round($float, 2);

        return number_format($num, 2);
    }
}

if (!function_exists('dumpAndDie')) {
    /**
     * @param $var
     */
    function dumpAndDie($var)
    {
        pr($var);
        exit();
    }
}

if (!function_exists('humanize')) {
    /**
     * @param $string
     * @return \Quantum\QString
     */
    function humanize($string)
    {
        return qs($string)->humanize()->toStdString();
    }
}

if (!function_exists('remove_duplicates')) {
    /**
     * @param $string
     * @return string
     */
    function remove_duplicates($string)
    {
        $a = qs($string)->explode(" ");

        $a = array_unique($a);

        return implode("", $a);
    }
}

if (!function_exists('to_password')) {
    /**
     * @param $string
     * @param $salt
     * @param string $algo
     * @return string
     */
    function to_password($string, $salt, $algo = 'ripemd160')
    {

        return hash($algo, $string . $salt);

    }
}

if (!function_exists('logger')) {
    /**
     * @param $message
     */
    function logger($message)
    {
        Quantum\Logger::logError($message);
    }
}

if (!function_exists('devlog')) {
    /**
     * @param $message
     */
    function devlog($message)
    {
        Quantum\Logger::dev($message);
    }
}

if (!function_exists('throwInvalidParametersIfEmpty')) {
    /**
     * @param $var
     */
    function throwInvalidParametersIfEmpty($var)
    {
        Quantum\Exception::throwInvalidParametersIfEmpty($var);
    }
}

if (!function_exists('throwResourceNotFoundIfEmpty')) {
    /**
     * @param $var
     */
    function throwResourceNotFoundIfEmpty($var)
    {
        Quantum\Exception::throwResourceNotFoundIfEmpty($var);
    }
}

if (!function_exists('is_positive')) {
    /**
     * @param $num
     * @return bool
     */
    function is_positive($num)
    {
        if (is_numeric($num)) {
            if ($num > 0)
                return true;
        }

        return false;
    }
}

if (!function_exists('is_positive_and_below')) {
    /**
     * @param $num
     * @param $max
     * @return bool
     */
    function is_positive_and_below($num, $max)
    {
        if (is_positive($num)) {
            if ($num < $max)
                return true;
        }

        return false;
    }
}

if (!function_exists('qm_profiler_start')) {
    /**
     * @param $timer_name
     */
    function qm_profiler_start($timer_name)
    {
        Quantum\Profiler::start($timer_name);
    }
}

if (!function_exists('qm_profiler_stop')) {
    /**
     * @param $timer_name
     */
    function qm_profiler_stop($timer_name)
    {
        Quantum\Profiler::stop($timer_name);
    }
}

if (!function_exists('qm_profiler_enable')) {
    /**
     *
     */
    function qm_profiler_enable()
    {
        Quantum\Profiler::enable();
    }
}

if (!function_exists('qm_profiler_disable')) {
    /**
     *
     */
    function qm_profiler_disable()
    {
        Quantum\Profiler::disable();
    }
}

if (!function_exists('qm_profiler_html')) {
    /**
     *
     */
    function qm_profiler_html()
    {
        echo Quantum\Profiler::toHtml();
    }
}

if (!function_exists('throw_exception')) {
    /**
     * @param $msg
     * @throws Exception
     */
    function throw_exception($msg)
    {
        throw new Exception($msg);
    }
}

if (!function_exists('is_closure')) {

    /**
     * @param $t
     * @return bool
     */
    function is_closure($t)
    {
        return is_object($t) && ($t instanceof Closure);
    }

}

if (!function_exists('instance_of')) {

    /**
     * @param $obj
     * @param $className
     * @return bool
     */
    function instance_of($obj, $className)
    {
        return is_object($obj) && ($obj instanceof $className);
    }

}


if (!function_exists('is_psr7_response')) {

    /**
     * @param $t
     * @return bool
     */
    function is_psr7_response($t)
    {
        return is_object($t) && ($t instanceof \Quantum\Psr7\Response);
    }

}

if (!function_exists('redirect')) {

    /**
     * @param $t
     * @return \Quantum\Psr7\Response
     */
    function redirect($url)
    {
        return new \Quantum\Psr7\Response\RedirectResponse($url);
    }

}

if (!function_exists('response')) {


    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return \Quantum\Psr7\Response\EmptyResponse|\Quantum\Psr7\Response\HtmlResponse|\Quantum\Psr7\Response\JsonResponse|\Quantum\Psr7\Response\RedirectResponse|\Quantum\Psr7\Response\XmlResponse
     */
    function response($contents, $status = 200, array $headers = [])
    {
        return \Quantum\Psr7\ResponseFactory::fromVariableData($contents, $status, $headers);
    }

}


//Laravel Helpers

if (!function_exists('append_config')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param array $array
     * @return array
     */
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;

                $array[$start] = array_pull($array, $key);
            }
        }

        return $array;
    }
}

if (!function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        if (is_null(get($array, $key))) {
            set($array, $key, $value);
        }

        return $array;
    }
}

if (!function_exists('array_build')) {
    /**
     * Build a new array using a callback.
     *
     * @param array $array
     * @param \Closure $callback
     * @return array
     */
    function array_build($array, Closure $callback)
    {
        $results = array();

        foreach ($array as $key => $value) {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }
}

if (!function_exists('array_divide')) {
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     * @return array
     */
    function array_divide($array)
    {
        return array(array_keys($array), array_values($array));
    }
}

if (!function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array
     * @param string $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array)$keys));
    }
}

if (!function_exists('array_fetch')) {
    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    function array_fetch($array, $key)
    {
        $results = array();

        foreach (explode('.', $key) as $segment) {
            foreach ($array as $value) {
                if (array_key_exists($segment, $value = (array)$value)) {
                    $results[] = $value[$segment];
                }
            }

            $array = array_values($results);
        }

        return array_values($results);
    }
}

if (!function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array $array
     * @param \Closure $callback
     * @param mixed $default
     * @return mixed
     */
    function array_first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) return $value;
        }

        return value($default);
    }
}

if (!function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array $array
     * @param \Closure $callback
     * @param mixed $default
     * @return mixed
     */
    function array_last($array, $callback, $default = null)
    {
        return first(array_reverse($array), $callback, $default);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param array $array
     * @return array
     */
    function array_flatten($array)
    {
        $return = array();

        array_walk_recursive($array, function ($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }
}

if (!function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    function array_forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array)$keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }
}

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if (!function_exists('array_has')) {
    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    function array_has($array, $key)
    {
        if (empty($array) || is_null($key)) return false;

        if (array_key_exists($key, $array)) return true;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param array $array
     * @param string $value
     * @param string $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        $results = array();

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if (!function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function array_pull(&$array, $key, $default = null)
    {
        $value = get($array, $key, $default);

        forget($array, $key);

        return $value;
    }
}

if (!function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if (!function_exists('array_where')) {
    /**
     * Filter the array using the given Closure.
     *
     * @param array $array
     * @param \Closure $callback
     * @return array
     */
    function array_where($array, Closure $callback)
    {
        $filtered = array();

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) $filtered[$key] = $value;
        }

        return $filtered;
    }
}

if (!function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param string $value
     * @return string
     */
    function camel_case($value)
    {
        static $camelCache = [];

        if (isset($camelCache[$value])) {
            return $camelCache[$value];
        }

        return $camelCache[$value] = lcfirst(studly($value));
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     *
     * @param string $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) return $target;

        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (!isset($target[$segment])) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return value($default);
                }

                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     * @return string
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle))) return true;
        }

        return false;
    }
}

if (!function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param array $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if (!function_exists('last')) {
    /**
     * Get the last element from an array.
     *
     * @param array $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if (!function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param object $object
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') return $object;

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (!function_exists('preg_replace_sub')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param string $pattern
     * @param array $replacements
     * @param string $subject
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            return array_shift($replacements);

        }, $subject);
    }
}

if (!function_exists('snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    function snake_case($value, $delimiter = '_')
    {
        static $snakeCache = [];
        $key = $value . $delimiter;

        if (isset($snakeCache[$key])) {
            return $snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $value));
        }

        return $snakeCache[$key] = $value;
    }
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }
}

if (!function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) return true;
        }

        return false;
    }
}

if (!function_exists('str_finish')) {
    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $cap
     * @return string
     */
    function str_finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/', '', $value) . $cap;
    }
}

if (!function_exists('str_is')) {
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    function str_is($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool)preg_match('#^' . $pattern . '#', $value);
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strlen($value) <= $limit) return $value;

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . $end;
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     * @return string
     *
     * @throws \RuntimeException
     */
    function str_random($length = 16)
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new RuntimeException('OpenSSL extension is required.');
        }

        $bytes = openssl_random_pseudo_bytes($length * 2);

        if ($bytes === false) {
            throw new RuntimeException('Unable to generate random string.');
        }

        return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
    }
}

if (!function_exists('str_replace_array')) {
    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param string $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    function str_replace_array($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
        }

        return $subject;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $title
     * @param string $separator
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        $title = ascii($title);

        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }
}

if (!function_exists('ascii')) {
    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param string $value
     * @return string
     */
    function ascii($value)
    {
        foreach (charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }
}

if (!function_exists('charsArray')) {
    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
     *
     * @return array
     */
    function charsArray()
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0' => ['°', '₀', '۰'],
            '1' => ['¹', '₁', '۱'],
            '2' => ['²', '₂', '۲'],
            '3' => ['³', '₃', '۳'],
            '4' => ['⁴', '₄', '۴', '٤'],
            '5' => ['⁵', '₅', '۵', '٥'],
            '6' => ['⁶', '₆', '۶', '٦'],
            '7' => ['⁷', '₇', '۷'],
            '8' => ['⁸', '₈', '۸'],
            '9' => ['⁹', '₉', '۹'],
            'a' => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'],
            'b' => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
            'c' => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
            'd' => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'],
            'f' => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
            'g' => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
            'h' => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'],
            'j' => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
            'k' => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
            'l' => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
            'm' => ['м', 'μ', 'م', 'မ', 'მ'],
            'n' => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'],
            'p' => ['п', 'π', 'ပ', 'პ', 'پ'],
            'q' => ['ყ'],
            'r' => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
            's' => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
            't' => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'],
            'v' => ['в', 'ვ', 'ϐ'],
            'w' => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
            'x' => ['χ', 'ξ'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
            'z' => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
            'aa' => ['ع', 'आ', 'آ'],
            'ae' => ['ä', 'æ', 'ǽ'],
            'ai' => ['ऐ'],
            'at' => ['@'],
            'ch' => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj' => ['ђ', 'đ'],
            'dz' => ['џ', 'ძ'],
            'ei' => ['ऍ'],
            'gh' => ['غ', 'ღ'],
            'ii' => ['ई'],
            'ij' => ['ĳ'],
            'kh' => ['х', 'خ', 'ხ'],
            'lj' => ['љ'],
            'nj' => ['њ'],
            'oe' => ['ö', 'œ', 'ؤ'],
            'oi' => ['ऑ'],
            'oii' => ['ऒ'],
            'ps' => ['ψ'],
            'sh' => ['ш', 'შ', 'ش'],
            'shch' => ['щ'],
            'ss' => ['ß'],
            'sx' => ['ŝ'],
            'th' => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts' => ['ц', 'ც', 'წ'],
            'ue' => ['ü'],
            'uu' => ['ऊ'],
            'ya' => ['я'],
            'yu' => ['ю'],
            'zh' => ['ж', 'ჟ', 'ژ'],
            '(c)' => ['©'],
            'A' => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'],
            'B' => ['Б', 'Β', 'ब'],
            'C' => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
            'D' => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
            'E' => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'],
            'F' => ['Ф', 'Φ'],
            'G' => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
            'H' => ['Η', 'Ή', 'Ħ'],
            'I' => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'],
            'K' => ['К', 'Κ'],
            'L' => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
            'M' => ['М', 'Μ'],
            'N' => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
            'O' => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'],
            'P' => ['П', 'Π'],
            'R' => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
            'S' => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
            'T' => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
            'U' => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'],
            'V' => ['В'],
            'W' => ['Ω', 'Ώ', 'Ŵ'],
            'X' => ['Χ', 'Ξ'],
            'Y' => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
            'Z' => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
            'AE' => ['Ä', 'Æ', 'Ǽ'],
            'CH' => ['Ч'],
            'DJ' => ['Ђ'],
            'DZ' => ['Џ'],
            'GX' => ['Ĝ'],
            'HX' => ['Ĥ'],
            'IJ' => ['Ĳ'],
            'JX' => ['Ĵ'],
            'KH' => ['Х'],
            'LJ' => ['Љ'],
            'NJ' => ['Њ'],
            'OE' => ['Ö', 'Œ'],
            'PS' => ['Ψ'],
            'SH' => ['Ш'],
            'SHCH' => ['Щ'],
            'SS' => ['ẞ'],
            'TH' => ['Þ'],
            'TS' => ['Ц'],
            'UE' => ['Ü'],
            'YA' => ['Я'],
            'YU' => ['Ю'],
            'ZH' => ['Ж'],
            ' ' => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
        ];
    }
}

if (!function_exists('studly_case')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     * @return string
     */
    function studly_case($value)
    {
        static $studlyCache = [];
        $key = $value;

        if (isset($studlyCache[$key])) {
            return $studlyCache[$key];
        }

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return $studlyCache[$key] = str_replace(' ', '', $value);
    }
}

if (!function_exists('title_case')) {
    /**
     * Convert a value to title case.
     *
     * @param string $value
     * @return string
     */
    function title_case($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits
     *
     * @param string $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given object. Useful for chaining.
     *
     * @param mixed $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}

/**
 * Helper functions for the helper functions, that can still be used standalone
 */
if (!function_exists('studly')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     * @return string
     */
    function studly($value)
    {
        static $studlyCache = [];
        $key = $value;

        if (isset($studlyCache[$key])) {
            return $studlyCache[$key];
        }

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return $studlyCache[$key] = str_replace(' ', '', $value);
    }
}

if (!function_exists('get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if (!function_exists('set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if (!function_exists('dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array
     * @param string $prepend
     * @return array
     */
    function dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array $array
     * @param \Closure $callback
     * @param mixed $default
     * @return mixed
     */
    function first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) return $value;
        }

        return value($default);
    }
}

if (!function_exists('forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    function forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array)$keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Password hash the given value.
     *
     * @param string $value
     * @param array $options
     * @return string
     *
     * @throws \RuntimeException
     */
    function bcrypt($value, $options = [])
    {
        $cost = isset($options['rounds']) ? $options['rounds'] : 10;

        $hashedValue = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hashedValue === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hashedValue;
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed $value
     * @param callable $callback
     * @return mixed
     */
    function tap($value, $callback)
    {
        $callback($value);

        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed
     * @return void
     */
    function dd()
    {


        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());

        var_dump(debug_backtrace());
        die(1);
    }
}


if (!function_exists('int8')) {
    /**
     * @param $i
     * @return false|string
     */
    function int8($i)
    {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }
}

if (!function_exists('uint8')) {
    /**
     * @param $i
     * @return false|string
     */
    function uint8($i)
    {
        return is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
    }
}

if (!function_exists('int16')) {
    /**
     * @param $i
     * @return false|string
     */
    function int16($i)
    {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }

}

if (!function_exists('uint16')) {
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint16($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("n", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("v", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("S", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }
}

if (!function_exists('int32')) {
    /**
     * @param $i
     * @return false|string
     */
    function int32($i)
    {
        return is_int($i) ? pack("l", $i) : unpack("l", $i)[1];
    }
}

if (!function_exists('uint32')) {
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint32($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("N", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("V", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("L", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

}

if (!function_exists('int64')) {
    /**
     * @param $i
     * @return false|string
     */
    function int64($i)
    {
        return is_int($i) ? pack("q", $i) : unpack("q", $i)[1];
    }

}

if (!function_exists('uint64')) {
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint64($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("J", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("P", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("Q", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

}


if (!function_exists('bin2bstr')) {
    /**
     * @param $input
     * @return false|string|null
     */
    function bin2bstr($input)
// Convert a binary expression (e.g., "100111") into a binary-string
    {
        if (!is_string($input)) return null; // Sanity check

        // Pack into a string
        return pack('H*', base_convert($input, 2, 16));
    }

}

if (!function_exists('bstr2bin')) {
    /**
     * @param $input
     * @return string|null
     */
    function bstr2bin($input)
// Binary representation of a binary-string
    {
        if (!is_string($input)) return null; // Sanity check

        // Unpack as a hexadecimal string
        $value = unpack('H*', $input);

        // Output binary representation
        return base_convert($value[1], 16, 2);
    }
}

if (!function_exists('concat')) {
    /**
     * @param $string1
     * @param string $string2
     * @param string $string3
     * @param string $string4
     * @param string $string5
     * @param string $string6
     * @param string $string7
     * @param string $string8
     * @param string $string9
     * @param string $string10
     * @return string
     */
    function concat($string1, $string2 = "", $string3 = "", $string4 = "", $string5 = "", $string6 = "", $string7 = "", $string8 = "", $string9 = "", $string10 = "")
    {
        if (is_array($string1)) {
            $strings = $string1;
        } else {
            $strings = array($string1, $string2, $string3, $string4, $string5, $string6, $string7, $string8, $string9, $string10);
        }

        return concat_arr($strings);
    }
}

if (!function_exists('concat_arr')) {
    /**
     * @param array $strings
     * @return string
     */
    function concat_arr(array $strings)
    {
        $concat = '';
        $count = count($strings);
        for ($i = 0; $i < $count; ++$i) {
            $concat .= $strings[$i];
        }

        return $concat;
    }
}

if (!function_exists('add_dollar_sign')) {
    /**
     * @param $string
     * @return string
     */
    function add_dollar_sign($string)
    {
        if (!qs($string)->startsWith('$'))
            return '$' . $string;

        return $string;
    }
}


if (!function_exists('get_called_class')) {
    /**
     * @return string
     */
    function get_called_class()
    {
        $bt = debug_backtrace();
        $l = 0;
        do {
            $l++;
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line'] - 1];
            preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $callerLine, $matches);
        } while ($matches[1] === 'parent' && $matches[1]);

        return $matches[1];
    }
}


if (!function_exists('recursive_read')) {
    /**
     * @param $directory
     * @return array|false
     */
    function recursive_read($directory)
    {
        return glob($directory . '/*', GLOB_ONLYDIR);
    }

}


if (!function_exists('pre')) {
    /**
     * @param $var
     */
    function pre($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
        exit();
    }

}

if (!function_exists('ensure_last_slash')) {
    /**
     * @param $url
     * @return string
     */
    function ensure_last_slash($url)
    {
        if (!ends_with($url, "/"))
            $url .= "/";

        return $url;
    }

}

if (!function_exists('deepscan')) {
    /**
     * @param $base_dir
     * @return array
     */
    function deepscan($base_dir)
    {
        $directories = array();
        foreach (scandir($base_dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $directories [] = $dir;
                $directories = array_merge($directories, deepscan($dir));
                if (!empty($directories))
                    $directories = array_map("ensure_last_slash", array_map('realpath', $directories));
            }
        }
        return $directories;
    }

}

if (!function_exists('deepscan_dirs')) {
    /**
     * @param $dirs
     * @return array
     */
    function deepscan_dirs($dirs)
    {
        $collection = array();

        foreach ($dirs as $dir) {
            array_push($collection, $dir);

            $children = deepscan($dir);

            if (!empty($children))
                $collection = array_map("ensure_last_slash", array_map('realpath', array_merge($collection, $children)));
        }

        return $collection;
    }

}


if (!function_exists('recursive_read_dirs')) {
    /**
     * @param $dirs
     * @return array
     */
    function recursive_read_dirs($dirs)
    {
        $collection = array();

        foreach ($dirs as $dir) {
            array_push($collection, $dir);

            $children = recursive_read($dir);

            if (!empty($children))
                $collection = array_map("ensure_last_slash", array_map('realpath', array_merge($collection, $children)));
        }

        return $collection;
    }

}


if (!function_exists('child_class_dir')) {
    /**
     * Get the child class path
     *
     * @param string|object $class
     * @return string
     */
    function child_class_dir($class)
    {
        $reflector = new \ReflectionClass(get_class($class));
        return dirname($reflector->getFileName());
    }
}


//Laravel Helpers

if ( ! function_exists('append_config'))
{
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value)
        {
            if (is_numeric($key))
            {
                $start++;

                $array[$start] = array_pull($array, $key);
            }
        }

        return $array;
    }
}

if ( ! function_exists('array_add'))
{
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        if (is_null(get($array, $key)))
        {
            set($array, $key, $value);
        }

        return $array;
    }
}

if ( ! function_exists('array_build'))
{
    /**
     * Build a new array using a callback.
     *
     * @param  array     $array
     * @param  \Closure  $callback
     * @return array
     */
    function array_build($array, Closure $callback)
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }
}

if ( ! function_exists('array_divide'))
{
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     */
    function array_divide($array)
    {
        return array(array_keys($array), array_values($array));
    }
}

if ( ! function_exists('array_dot'))
{
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $results = array_merge($results, dot($value, $prepend.$key.'.'));
            }
            else
            {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_except'))
{
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('array_fetch'))
{
    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  array   $array
     * @param  string  $key
     * @return array
     */
    function array_fetch($array, $key)
    {
        $results = array();

        foreach (explode('.', $key) as $segment)
        {
            foreach ($array as $value)
            {
                if (array_key_exists($segment, $value = (array) $value))
                {
                    $results[] = $value[$segment];
                }
            }

            $array = array_values($results);
        }

        return array_values($results);
    }
}

if ( ! function_exists('array_first'))
{
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array     $array
     * @param  \Closure  $callback
     * @param  mixed     $default
     * @return mixed
     */
    function array_first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value)
        {
            if (call_user_func($callback, $key, $value)) return $value;
        }

        return value($default);
    }
}

if ( ! function_exists('array_last'))
{
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array     $array
     * @param  \Closure  $callback
     * @param  mixed     $default
     * @return mixed
     */
    function array_last($array, $callback, $default = null)
    {
        return first(array_reverse($array), $callback, $default);
    }
}

if ( ! function_exists('array_flatten'))
{
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @return array
     */
    function array_flatten($array)
    {
        $return = array();

        array_walk_recursive($array, function($x) use (&$return) { $return[] = $x; });

        return $return;
    }
}

if ( ! function_exists('array_forget'))
{
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    function array_forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array) $keys as $key)
        {
            $parts = explode('.', $key);

            while (count($parts) > 1)
            {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part]))
                {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }
}

if ( ! function_exists('array_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if ( ! function_exists('array_has'))
{
    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @return bool
     */
    function array_has($array, $key)
    {
        if (empty($array) || is_null($key)) return false;

        if (array_key_exists($key, $array)) return true;

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }
}

if ( ! function_exists('array_only'))
{
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('array_pluck'))
{
    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string  $value
     * @param  string  $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        $results = array();

        foreach ($array as $item)
        {
            $itemValue = data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key))
            {
                $results[] = $itemValue;
            }
            else
            {
                $itemKey = data_get($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_pull'))
{
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_pull(&$array, $key, $default = null)
    {
        $value = get($array, $key, $default);

        forget($array, $key);

        return $value;
    }
}

if ( ! function_exists('array_set'))
{
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key]))
            {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if ( ! function_exists('array_where'))
{
    /**
     * Filter the array using the given Closure.
     *
     * @param  array     $array
     * @param  \Closure  $callback
     * @return array
     */
    function array_where($array, Closure $callback)
    {
        $filtered = array();

        foreach ($array as $key => $value)
        {
            if (call_user_func($callback, $key, $value)) $filtered[$key] = $value;
        }

        return $filtered;
    }
}

if ( ! function_exists('camel_case'))
{
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    function camel_case($value)
    {
        static $camelCache = [];

        if (isset($camelCache[$value]))
        {
            return $camelCache[$value];
        }

        return $camelCache[$value] = lcfirst(studly($value));
    }
}

if ( ! function_exists('class_basename'))
{
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if ( ! function_exists('class_uses_recursive'))
{
    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     *
     * @param  string  $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class)
        {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if ( ! function_exists('data_get'))
{
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) return $target;

        foreach (explode('.', $key) as $segment)
        {
            if (is_array($target))
            {
                if ( ! array_key_exists($segment, $target))
                {
                    return value($default);
                }

                $target = $target[$segment];
            }
            elseif ($target instanceof ArrayAccess)
            {
                if ( ! isset($target[$segment]))
                {
                    return value($default);
                }

                $target = $target[$segment];
            }
            elseif (is_object($target))
            {
                if ( ! isset($target->{$segment}))
                {
                    return value($default);
                }

                $target = $target->{$segment};
            }
            else
            {
                return value($default);
            }
        }

        return $target;
    }
}

if ( ! function_exists('e'))
{
    /**
     * Escape HTML entities in a string.
     *
     * @param  string  $value
     * @return string
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if ( ! function_exists('ends_with'))
{
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ((string) $needle === substr($haystack, -strlen($needle))) return true;
        }

        return false;
    }
}

if ( ! function_exists('head'))
{
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if ( ! function_exists('last'))
{
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if ( ! function_exists('object_get'))
{
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object  $object
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') return $object;

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_object($object) || ! isset($object->{$segment}))
            {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if ( ! function_exists('preg_replace_sub'))
{
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function() use (&$replacements)
        {
            return array_shift($replacements);

        }, $subject);
    }
}

if ( ! function_exists('snake_case'))
{
    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    function snake_case($value, $delimiter = '_')
    {
        static $snakeCache = [];
        $key = $value.$delimiter;

        if (isset($snakeCache[$key]))
        {
            return $snakeCache[$key];
        }

        if ( ! ctype_lower($value))
        {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
        }

        return $snakeCache[$key] = $value;
    }
}

if ( ! function_exists('starts_with'))
{
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }
}

if ( ! function_exists('str_contains'))
{
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function str_contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle != '' && strpos($haystack, $needle) !== false) return true;
        }

        return false;
    }
}

if ( ! function_exists('str_finish'))
{
    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    function str_finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
    }
}

if ( ! function_exists('str_is'))
{
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    function str_is($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern).'\z';

        return (bool) preg_match('#^'.$pattern.'#', $value);
    }
}

if ( ! function_exists('str_limit'))
{
    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int     $limit
     * @param  string  $end
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strlen($value) <= $limit) return $value;

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
    }
}

if ( ! function_exists('str_random'))
{
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     */
    function str_random($length = 16)
    {
        if ( ! function_exists('openssl_random_pseudo_bytes'))
        {
            throw new RuntimeException('OpenSSL extension is required.');
        }

        $bytes = openssl_random_pseudo_bytes($length * 2);

        if ($bytes === false)
        {
            throw new RuntimeException('Unable to generate random string.');
        }

        return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
    }
}

if ( ! function_exists('str_replace_array'))
{
    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string  $search
     * @param  array   $replace
     * @param  string  $subject
     * @return string
     */
    function str_replace_array($search, array $replace, $subject)
    {
        foreach ($replace as $value)
        {
            $subject = preg_replace('/'.$search.'/', $value, $subject, 1);
        }

        return $subject;
    }
}

if ( ! function_exists('str_slug'))
{
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        $title = ascii($title);

        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }
}

if ( ! function_exists('ascii'))
{
    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string  $value
     * @return string
     */
    function ascii($value)
    {
        foreach (charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }
}

if ( ! function_exists('charsArray'))
{
    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
     *
     * @return array
     */
    function charsArray()
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0'    => ['°', '₀', '۰'],
            '1'    => ['¹', '₁', '۱'],
            '2'    => ['²', '₂', '۲'],
            '3'    => ['³', '₃', '۳'],
            '4'    => ['⁴', '₄', '۴', '٤'],
            '5'    => ['⁵', '₅', '۵', '٥'],
            '6'    => ['⁶', '₆', '۶', '٦'],
            '7'    => ['⁷', '₇', '۷'],
            '8'    => ['⁸', '₈', '۸'],
            '9'    => ['⁹', '₉', '۹'],
            'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'],
            'b'    => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
            'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
            'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
            'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'],
            'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
            'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
            'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
            'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'],
            'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
            'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
            'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
            'm'    => ['м', 'μ', 'م', 'မ', 'მ'],
            'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
            'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'],
            'p'    => ['п', 'π', 'ပ', 'პ', 'پ'],
            'q'    => ['ყ'],
            'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
            's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
            't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
            'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'],
            'v'    => ['в', 'ვ', 'ϐ'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
            'x'    => ['χ', 'ξ'],
            'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
            'aa'   => ['ع', 'आ', 'آ'],
            'ae'   => ['ä', 'æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'at'   => ['@'],
            'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['ö', 'œ', 'ؤ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ', 'ش'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'ue'   => ['ü'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ', 'ژ'],
            '(c)'  => ['©'],
            'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'],
            'B'    => ['Б', 'Β', 'ब'],
            'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
            'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
            'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'],
            'F'    => ['Ф', 'Φ'],
            'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
            'H'    => ['Η', 'Ή', 'Ħ'],
            'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'],
            'K'    => ['К', 'Κ'],
            'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
            'M'    => ['М', 'Μ'],
            'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
            'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'],
            'P'    => ['П', 'Π'],
            'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
            'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
            'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
            'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'],
            'V'    => ['В'],
            'W'    => ['Ω', 'Ώ', 'Ŵ'],
            'X'    => ['Χ', 'Ξ'],
            'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
            'AE'   => ['Ä', 'Æ', 'Ǽ'],
            'CH'   => ['Ч'],
            'DJ'   => ['Ђ'],
            'DZ'   => ['Џ'],
            'GX'   => ['Ĝ'],
            'HX'   => ['Ĥ'],
            'IJ'   => ['Ĳ'],
            'JX'   => ['Ĵ'],
            'KH'   => ['Х'],
            'LJ'   => ['Љ'],
            'NJ'   => ['Њ'],
            'OE'   => ['Ö', 'Œ'],
            'PS'   => ['Ψ'],
            'SH'   => ['Ш'],
            'SHCH' => ['Щ'],
            'SS'   => ['ẞ'],
            'TH'   => ['Þ'],
            'TS'   => ['Ц'],
            'UE'   => ['Ü'],
            'YA'   => ['Я'],
            'YU'   => ['Ю'],
            'ZH'   => ['Ж'],
            ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
        ];
    }
}

if ( ! function_exists('studly_case'))
{
    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    function studly_case($value)
    {
        static $studlyCache = [];
        $key = $value;

        if (isset($studlyCache[$key]))
        {
            return $studlyCache[$key];
        }

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return $studlyCache[$key] = str_replace(' ', '', $value);
    }
}

if (! function_exists('title_case')) {
    /**
     * Convert a value to title case.
     *
     * @param  string  $value
     * @return string
     */
    function title_case($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}

if ( ! function_exists('trait_uses_recursive'))
{
    /**
     * Returns all traits used by a trait and its traits
     *
     * @param  string  $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait)
        {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if ( ! function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if ( ! function_exists('with'))
{
    /**
     * Return the given object. Useful for chaining.
     *
     * @param  mixed  $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}

/**
 * Helper functions for the helper functions, that can still be used standalone
 */
if ( ! function_exists('studly'))
{
    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    function studly($value)
    {
        static $studlyCache = [];
        $key = $value;

        if (isset($studlyCache[$key]))
        {
            return $studlyCache[$key];
        }

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return $studlyCache[$key] = str_replace(' ', '', $value);
    }
}

if ( ! function_exists('get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if ( ! function_exists('set'))
{
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key]))
            {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if ( ! function_exists('dot'))
{
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $results = array_merge($results, dot($value, $prepend.$key.'.'));
            }
            else
            {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}

if ( ! function_exists('first'))
{
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array     $array
     * @param  \Closure  $callback
     * @param  mixed     $default
     * @return mixed
     */
    function first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value)
        {
            if (call_user_func($callback, $key, $value)) return $value;
        }

        return value($default);
    }
}

if ( ! function_exists('forget'))
{
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    function forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array) $keys as $key)
        {
            $parts = explode('.', $key);

            while (count($parts) > 1)
            {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part]))
                {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }
}

if (! function_exists('bcrypt')) {
    /**
     * Password hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     *
     * @throws \RuntimeException
     */
    function bcrypt($value, $options = [])
    {
        $cost = isset($options['rounds']) ? $options['rounds'] : 10;

        $hashedValue = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hashedValue === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hashedValue;
    }
}

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @return mixed
     */
    function tap($value, $callback)
    {
        $callback($value);

        return $value;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(function($x) {
            var_dump($x);
        }, func_get_args());

        die(1);
    }
}



if (! function_exists('int8')) 
{
    /**
     * @param $i
     * @return false|string
     */
    function int8($i)
    {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }
}

if (! function_exists('uint8'))
{
    /**
     * @param $i
     * @return false|string
     */
    function uint8($i)
    {
        return is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
    }
}

if (! function_exists('int16'))
{
    /**
     * @param $i
     * @return false|string
     */
    function int16($i)
    {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }

}

if (! function_exists('uint16'))
{
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint16($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("n", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("v", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("S", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }
}

if (! function_exists('int32'))
{
    /**
     * @param $i
     * @return false|string
     */
    function int32($i)
    {
        return is_int($i) ? pack("l", $i) : unpack("l", $i)[1];
    }
}

if (! function_exists('uint32')) {
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint32($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("N", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("V", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("L", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

}

if (! function_exists('int64'))
{
    /**
     * @param $i
     * @return false|string
     */
    function int64($i)
    {
        return is_int($i) ? pack("q", $i) : unpack("q", $i)[1];
    }

}

if (! function_exists('uint64'))
{
    /**
     * @param $i
     * @param bool $endianness
     * @return int|mixed
     */
    function uint64($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("J", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("P", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("Q", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

}


if (! function_exists('bin2bstr'))
{
    /**
     * @param $input
     * @return false|string|null
     */
    function bin2bstr($input)
// Convert a binary expression (e.g., "100111") into a binary-string
    {
        if (!is_string($input)) return null; // Sanity check

        // Pack into a string
        return pack('H*', base_convert($input, 2, 16));
    }

}

if (! function_exists('bstr2bin'))
{
    /**
     * @param $input
     * @return string|null
     */
    function bstr2bin($input)
// Binary representation of a binary-string
    {
        if (!is_string($input)) return null; // Sanity check

        // Unpack as a hexadecimal string
        $value = unpack('H*', $input);

        // Output binary representation
        return base_convert($value[1], 16, 2);
    }
}

if (! function_exists('concat'))
{
    /**
     * @param $string1
     * @param string $string2
     * @param string $string3
     * @param string $string4
     * @param string $string5
     * @param string $string6
     * @param string $string7
     * @param string $string8
     * @param string $string9
     * @param string $string10
     * @return string
     */
    function concat($string1, $string2 = "", $string3 = "", $string4 = "", $string5 = "", $string6 = "", $string7 = "", $string8 = "", $string9 = "", $string10 = "")
    {
        if (is_array($string1))
        {
            $strings = $string1;
        }
        else
        {
            $strings = array($string1, $string2, $string3, $string4, $string5, $string6, $string7, $string8, $string9, $string10);
        }

        return concat_arr($strings);
    }
}

if (! function_exists('concat_arr'))
{
    /**
     * @param array $strings
     * @return string
     */
    function concat_arr(array $strings)
    {
        $concat = '';
        $count = count($strings);
        for ($i = 0; $i < $count; ++$i)
        {
            $concat .= $strings[$i];
        }

        return $concat;
    }
}

if (!function_exists('add_dollar_sign'))
{
    /**
     * @param $string
     * @return string
     */
    function add_dollar_sign($string)
    {
        if (!qs($string)->startsWith('$'))
            return '$'.$string;

        return $string;
    }
}


if (!function_exists('get_called_class'))
{
    /**
     * @return string
     */
    function get_called_class()
    {
        $bt = debug_backtrace();
        $l = 0;
        do
        {
            $l++;
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line']-1];
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/', $callerLine, $matches);
        } while ($matches[1] === 'parent' && $matches[1]);

        return $matches[1];
    }
}


if (!function_exists('recursive_read'))
{
    /**
     * @param $directory
     * @return array|false
     */
    function recursive_read($directory)
    {
       return glob($directory . '/*' , GLOB_ONLYDIR);
    }

}




if (!function_exists('pre'))
{
    /**
     * @param $var
     */
    function pre($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
        exit();
    }

}

if (!function_exists('ensure_last_slash'))
{
    /**
     * @param $url
     * @return string
     */
    function ensure_last_slash($url)
    {
        if (!ends_with($url, "/"))
            $url .= "/";

        return $url;
    }

}

if (!function_exists('deepscan'))
{
    /**
     * @param $base_dir
     * @return array
     */
    function deepscan($base_dir)
    {
        $directories = array();
        foreach(scandir($base_dir) as $file) {
            if($file == '.' || $file == '..') continue;
            $dir = $base_dir.DIRECTORY_SEPARATOR.$file;
            if(is_dir($dir)) {
                $directories []= $dir;
                $directories = array_merge($directories, deepscan($dir));
                if (!empty($directories))
                    $directories = array_map("ensure_last_slash", array_map('realpath', $directories));
            }
        }
        return $directories;
    }

}

if (!function_exists('deepscan_dirs'))
{
    /**
     * @param $dirs
     * @return array
     */
    function deepscan_dirs($dirs)
    {
        $collection = array();

        foreach ($dirs as $dir)
        {
            array_push($collection, $dir);

            $children = deepscan($dir);

            if (!empty($children))
                $collection = array_map("ensure_last_slash", array_map('realpath', array_merge($collection, $children)));
        }

        return $collection;
    }

}


if (!function_exists('recursive_read_dirs'))
{
    /**
     * @param $dirs
     * @return array
     */
    function recursive_read_dirs($dirs)
    {
        $collection = array();

        foreach ($dirs as $dir)
        {
            array_push($collection, $dir);

            $children = recursive_read($dir);

            if (!empty($children))
                $collection = array_map("ensure_last_slash", array_map('realpath', array_merge($collection, $children)));
        }

        return $collection;
    }

}


if (!function_exists('child_class_dir'))
{
    /**
     * Get the child class path
     *
     * @param  string|object  $class
     * @return string
     */
    function child_class_dir($class)
    {
        $reflector = new \ReflectionClass(get_class($class));
        return dirname($reflector->getFileName());
    }
}

if ( ! function_exists('set_simplexml_attribute'))
{

    function set_simplexml_attribute(SimpleXMLElement $node, $attributeName, $attributeValue)
    {
        $attributes = $node->attributes();
        if (isset($attributes->$attributeName)) {
            $attributes->$attributeName = $attributeValue;
        } else {
            $node->addAttribute($attributeName, $attributeValue);
        }
    }

}

if (!function_exists('is_countable'))
{

    function is_countable($c)
    {
        return is_array($c) || $c instanceof Countable;
    }

}


if (!function_exists('to_negative'))
{

    function to_negative($num)
    {
        return $num <= 0 ? $num : -$num;
    }

}


if (!function_exists('to_positive'))
{

    function to_positive($num)
    {
        return $num >= 0 ? $num : -$num;
    }

}


if (!function_exists('to_object'))
{
    /**
     * Convert an array into an object
     *
     * @param  array  $array
     * @return object
     */
    function to_object($array)
    {
        return (object) $array;
    }
}



if (!function_exists('cli_echo'))
{

    /**
     * @param $string
     */
    function cli_echo($string)
    {
        echo $string . PHP_EOL;
    }
}

if (!function_exists('set_header'))
{
    /**
     * @param $key
     * @param $value
     */
    function set_header($key, $value)
    {
        header($key.': '.$value);
    }
}


if (!function_exists('apache_request_headers'))
{
    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     *
     * @return array The HTTP header key/value pairs.
     */
    function apache_request_headers()
    {
        $headers = array();

        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }

}


if (!function_exists('getallheaders')) {

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @return array The HTTP header key/value pairs.
     */
    function getallheaders()
    {
        return call_user_func('apache_request_headers');
    }
}



if (!function_exists('is_serialized'))
{
    function is_serialized( $data, $strict = true ) {
        // If it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' === $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }
            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $data[0];
        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
        }
        return false;
    }
}

if (!function_exists('maybe_serialize'))
{
    function maybe_serialize( $data ) {
        if ( is_array( $data ) || is_object( $data ) ) {
            return serialize( $data );
        }

        return $data;
    }

}

if (!function_exists('maybe_unserialize'))
{
    function maybe_unserialize($data)
    {
        if (is_serialized($data)) {
            return @unserialize(trim($data));
        }

        return $data;
    }
}


if (!function_exists('header_timestamp'))
{
    function header_timestamp()
    {
        return gmdate('D, d M Y H:i:s T');
    }
}


if (!function_exists('clear_cache'))
{
    function clear_cache($key = null)
    {
        if ($key) {
            if (Quantum\Cache::has($key)) {
                Quantum\Cache::delete($key);
            }
        }
        else {
            Quantum\Cache::flush();
        }
    }
}

if (!function_exists('from_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_cache($key, $callback, $expiration = 300)
    {
        if (has_request_param('flush_current_objects_cache'))
            clear_cache($key); //TODO MOVE THIS INTO A MIDDLEWARE

        return Quantum\Cache::get($key, $callback, $expiration);
    }
}

if (!function_exists('from_app_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_app_cache($key, $callback, $expiration = 300)
    {
        $app_config = \QM::config()->getHostedAppConfig();

        if ($app_config)
            $key = $key.'@'.$app_config['uri'];

        return from_cache($key, $callback, $expiration);
    }
}


if (!function_exists('from_account_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_account_cache($key, $callback, $expiration = 300)
    {
        $user = Auth::getUserFromSession();

        if ($user)
            $key = $key.'@Account'.$user->getAccountId();

        return from_app_cache($key, $callback, $expiration);
    }
}

if (!function_exists('from_user_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_user_cache($key, $callback, $expiration = 300)
    {
        $user = Auth::getUserFromSession();

        if ($user)
            $key = $key.'@User'.$user->getId();

        return from_account_cache($key, $callback, $expiration);
    }
}

if (!function_exists('from_visitor_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_visitor_cache($key, $callback, $expiration = 300)
    {
        $fingerprint = QM::request()->getVisitorFingerprint();

        if ($fingerprint)
            $key = $key.'@Visitor'.$fingerprint;

        return from_app_cache($key, $callback, $expiration);
    }
}


if (!function_exists('in_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function in_cache($key)
    {
        return \Quantum\Cache::has($key);
    }
}


if (!function_exists('cache'))
{
    /**
     * @param $key
     * @param $data
     * @param int $expiration
     * @return mixed
     */
    function cache($key, $data, $expiration)
    {
        return \Quantum\Cache::set($key, $data, $expiration);
    }
}


if (!function_exists('from_runtime_registry'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_runtime_registry($key, $data = null)
    {
        $registry = \Quantum\RuntimeRegistry::getInstance();

        $stored_data = $registry->get($key, null);

        if ($stored_data === null)
        {
            if (is_closure($data))
            {
                $value = call_user_func($data);
                $registry->set($key, $value);
                return $value;
            }
            elseif ($data)
            {
                $registry->set($key, $data);
                return $data;
            }
        }

        return $stored_data;
    }
}

if (!function_exists('to_runtime_registry'))
{
    function to_runtime_registry($key, $data)
    {
        $registry = \Quantum\RuntimeRegistry::getInstance();

        if (is_closure($data))
        {
            $value = call_user_func($data);
            $registry->set($key, $value);
            return $value;
        }
        else
        {
            $registry->set($key, $data);
            return $data;
        }
    }
}

if (!function_exists('in_runtime_registry'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function in_runtime_registry($key)
    {
        return from_runtime_registry($key) != null;
    }
}




if (!function_exists('from_memcache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_memcache($key, $data, $expiration = 300)
    {
        $provider = \Quantum\Cache\Backend\Memcache::getInstance();

        $stored_data = $provider->get($key);

        if ($stored_data === null)
        {
            if (is_closure($data))
            {
                $value = call_user_func($data);
                $provider->set($key, $value, $expiration);
                return $value;
            }
            else
            {
                $provider->set($key, $data, $expiration);
                return $data;
            }
        }

        return $stored_data;
    }
}


if (!function_exists('to_memcache'))
{
    /**
     * @param $key
     * @param $data
     * @param int $expiration
     * @return mixed
     */
    function to_memcache($key, $data, $expiration)
    {
        return \Quantum\Cache\Backend\Memcache::getInstance()->set($key, $data, $expiration);
    }
}

if (!function_exists('from_redis'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_redis($key, $data, $expiration = 300)
    {
        $provider = \Quantum\Cache\Backend\Redis::getInstance();

        $stored_data = $provider->get($key);

        if ($stored_data === null)
        {
            if (is_closure($data))
            {
                $value = call_user_func($data);
                $provider->set($key, $value, $expiration);
                return $value;
            }
            else
            {
                $provider->set($key, $data, $expiration);
                return $data;
            }
        }

        return $stored_data;
    }

}

if (!function_exists('to_redis'))
{
    /**
     * @param $key
     * @param $data
     * @param int $expiration
     * @return mixed
     */
    function to_redis($key, $data, $expiration)
    {
        return \Quantum\Cache\Backend\Redis::getInstance()->set($key, $data, $expiration);
    }
}


if (!function_exists('from_files_cache'))
{
    /**
     * @param $key
     * @param $callback
     * @param int $expiration
     * @return mixed
     */
    function from_files_cache($key, $data, $expiration = 300)
    {
        $provider = \Quantum\Cache\Backend\FilesBasedCacheStorage::getInstance();

        $stored_data = $provider->get($key);

        if ($stored_data === null)
        {
            if (is_closure($data))
            {
                $value = call_user_func($data);
                $provider->set($key, $value, $expiration);
                return $value;
            }
            else
            {
                $provider->set($key, $data, $expiration);
                return $data;
            }
        }

        return $stored_data;
    }
}

if (!function_exists('to_files_cache'))
{
    /**
     * @param $key
     * @param $data
     * @param int $expiration
     * @return mixed
     */
    function to_files_cache($key, $data, $expiration)
    {
        return \Quantum\Cache\Backend\FilesBasedCacheStorage::getInstance()->set($key, $data, $expiration);
    }
}



if (!function_exists('get_kernel_setting'))
{

    function get_kernel_setting($key, $fallback = false)
    {
        return \Quantum\Config::getKernelSetting($key, $fallback);
    }
}

if (!function_exists('get_hosted_app_setting'))
{

    function get_hosted_app_setting($key, $fallback = false)
    {
        return \Quantum\Config::getHostedAppSetting($key, $fallback);
    }
}

if (!function_exists('get_active_app_setting'))
{

    function get_active_app_setting($key, $fallback = false)
    {
        return \Quantum\Config::getActiveAppSetting($key, $fallback);
    }
}

if (!function_exists('get_current_route_setting'))
{

    function get_current_route_setting($key, $fallback = false)
    {
        return \Quantum\Config::getCurrentRouteSetting($key, $fallback);
    }
}

if (!function_exists('current_route_has'))
{

    function current_route_has($key, $fallback = false)
    {
        $val = \Quantum\Config::getCurrentRouteSetting($key, $fallback);

        if ($val)
            return true;

        return false;
    }
}

if (!function_exists('get_current_environment_setting'))
{

    function get_current_environment_setting($key, $fallback = false)
    {
        return \Quantum\Config::getCurrentEnvironmentSetting($key, $fallback);
    }
}

if (!function_exists('get_overridable_app_setting'))
{
    function get_overridable_app_setting($key, $fallback = null)
    {
        $setting = get_active_app_setting($key);

        if (!$setting)
            $setting = get_hosted_app_setting($key);

        if ($setting !== false)
            return $setting;

        return $fallback;
    }
}


if (!function_exists('get_overridable_route_setting'))
{
    function get_overridable_route_setting($key, $fallback = null)
    {
        $setting = get_current_route_setting($key);

        if (!$setting)
            $setting = get_active_app_setting($key);

        if (!$setting)
            $setting = get_hosted_app_setting($key);

        if (!$setting)
            $setting = get_current_environment_setting($key);

        if (!$setting)
            $setting = get_kernel_setting($key);

        if ($setting !== false)
            return $setting;

        return $fallback;
    }
}


if (!function_exists('get_current_route'))
{
    function get_current_route()
    {
        return \Quantum\Config::getInstance()->getCurrentRoute();
    }
}


if (!function_exists('is_production_environment'))
{
    function is_production_environment()
    {
        return \Quantum\Config::getInstance()->isProductionEnvironment();
    }
}

if (!function_exists('is_development_environment'))
{
    function is_development_environment()
    {
        return \Quantum\Config::getInstance()->isDevelopmentEnvironment();
    }
}

if (!function_exists('observe_event_with_controller'))
{
    function observe_event_with_controller($event_key, $controllerName, $controllerMethod, $callOnlyOnce = false)
    {
        \Quantum\Events\EventsManager::getInstance()->addControllerObserver($event_key, $controllerName, $controllerMethod, $callOnlyOnce);
    }
}


if (!function_exists('observe_event'))
{
    function observe_event($event_key, $callable, $priority = 100, $callOnlyOnce = false)
    {
        \Quantum\Events\EventsManager::getInstance()->addObserver($event_key, $callable, $priority, $callOnlyOnce);
    }
}

if (!function_exists('observe_multiple_events'))
{
    function observe_multiple_events($events, $callable)
    {
        foreach ($events as $event) {
            \Quantum\Events\EventsManager::getInstance()->addObserver($event, $callable);
        }
    }
}

if (!function_exists('dispatch_event'))
{
    function dispatch_event($event_key, $data = null)
    {
        return \Quantum\Events\EventsManager::getInstance()->dispatch($event_key, $data);
    }
}



if (!function_exists('observe'))
{
    function observe($event_key, $callable)
    {
        observe_event($event_key, $callable);
    }
}

if (!function_exists('dispatch'))
{
    function dispatch($event_key, $data = null)
    {
        return dispatch_event($event_key, $data);
    }
}

if (!function_exists('is_event'))
{
    function is_event($event)
    {
        return (is_a($event, Quantum\Events\Event::class));
    }
}

if (!function_exists('say_hi'))
{
    function say_hi()
    {
        qs('Hi!')->render();
    }
}


if (!function_exists('call_if_exists'))
{
    function call_if_exists($object, $method_name)
    {
        if (method_exists($object, $method_name)) {
            call_user_func(array($object, $method_name));
        }
    }
}


if (!function_exists('is_cli'))
{
    function is_cli()
    {
        return Quantum\Request::getInstance()->isCommandLine();
    }
}












