<?php



namespace Quantum;

use Quantum\Middleware\ValidatePostSize;
use Quantum\Psr7\ServerRequestFactory;

require_once("Singleton.php");
require_once ("Security.php");
require_once ("Utilities.php");


/**
 * @param $key
 * @param $array
 * @return bool
 */
function has_key($key, $array)
{
    if (is_array($array) && array_key_exists($key, $array))
        return true;

    return false;
}

/**
 * Class Request
 * @package Quantum
 */
class Request extends Singleton
{
    /**
     *
     */
    const dontAddSlash = false;
    /**
     *
     */
    const addSlash = true;

    /**
     * @var
     */
    public $validation_errors_get;
    /**
     * @var
     */
    public $validation_errors_post;


    /**
     * Request constructor.
     */
    function __construct()
    {
        $this->_RAW_POST = $_POST;
        $this->_RAW_GET = $_GET;
        $this->_RAW_REQUEST = $_REQUEST;
        $this->_RAW_COOKIE = $_COOKIE;

        $this->_GET       = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        $this->_POST      = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $this->_COOKIE    = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING);

        $this->_SERVER    = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING);

        $this->_REQUEST   = (array)$this->_POST  + (array)$this->_GET + (array)$this->_COOKIE;

        if (!empty($this->_GET))
            $this->_GET    = Security::trim($this->_GET);

        if (!empty($this->_POST))
            $this->_POST    = Security::trim($this->_POST);

        if (!empty($this->_COOKIE))
            $this->_COOKIE    = Security::trim($this->_COOKIE);

        $_GET = $this->_GET;
        $_POST = $this->_POST;
        $_COOKIE = $this->_COOKIE;


        $this->setRequestUrl();
    }


    /**
     *
     */
    private function setRequestUrl()
    {
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']))
        {
            $this->requestUrl = array($this->getProtocol(true)."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

            if  ($this->hasParam("controller"))
                array_push($this->requestUrl, $this->getParam('controller'));

            if  ($this->hasParam("task"))
                array_push($this->requestUrl, $this->getParam('task'));

            if  ($this->hasParam("object_id"))
                array_push($this->requestUrl, $this->getParam('object_id'));

            if  ($this->hasParam("query_id"))
                array_push($this->requestUrl, $this->getParam('query_id'));
        }


    }


    /**
     * @return bool
     */
    public function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'GET');
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'PUT');
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'DELETE');
    }

    /**
     * @return bool
     */
    public function isPatch()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'PATCH');
    }

    /**
     * @return bool
     */
    public function isHead()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'HEAD');
    }

    /**
     * @return bool
     */
    public function isOptions()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'OPTIONS');
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getPostParam($paramName, $fallback = false)
    {
        if ($this->hasPostParam($paramName))
            return $this->_POST[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getGetParam($paramName, $fallback = false)
    {
        if ($this->hasGetParam($paramName))
            return $this->_GET[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool|mixed
     */
    public function getRequestParam($paramName, $fallback = false)
    {
        if ($this->hasRequestParam($paramName))
            return $this->_REQUEST[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getCookieParam($paramName, $fallback = false)
    {
        if ($this->hasCookieParam($paramName))
            return $this->_COOKIE[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getEncryptedCookieParam($paramName, $fallback = false)
    {
        if ($this->hasCookieParam($paramName))
        {
            $value = $this->_COOKIE[$paramName];

            if (!empty($value))
                $value = SystemEncryptor::decrypt($value);

            return $value;
        }

        return $fallback;
    }


    /**
     * @return mixed
     */
    public function getRawPost()
    {
        return $this->_RAW_POST;
    }

    /**
     * @return mixed
     */
    public function getRawGet()
    {
        return $this->_RAW_GET;
    }

    /**
     * @return mixed
     */
    public function getRawCookie()
    {
        return $this->_RAW_COOKIE;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getRawPostParam($paramName, $fallback = false)
    {
        if ($this->hasRawPostParam($paramName))
            return $this->_RAW_POST[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getRawGetParam($paramName, $fallback = false)
    {
        if ($this->hasRawGetParam($paramName))
            return $this->_RAW_GET[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getRawCookieParam($paramName, $fallback = false)
    {
        if ($this->hasRawCookieParam($paramName))
            return $this->_RAW_COOKIE[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool
     */
    public function getRawParam($paramName, $fallback = false)
    {
        if (has_key($paramName, $this->_RAW_REQUEST))
            return $this->_RAW_REQUEST[$paramName];

        return $fallback;
    }

    /**
     * @param $paramName
     * @param bool $fallback
     * @return bool|mixed
     */
    public function getParam($paramName, $fallback = false)
    {
        $p = $this->getPostParam($paramName);

        if (empty($p))
            $p = $this->getGetParam($paramName);

        if (empty($p))
            $p = $this->getRequestParam($paramName);

        if (empty($p))
            $p = $fallback;

        return $p;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isMissingParam($key)
    {
        return !$this->hasParam($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasParam($key)
    {
        if ($this->hasPostParam($key) ||
            $this->hasGetParam($key)  ||
            $this->hasRequestParam($key))
            return true;

        return false;
    }

    /**
     * @param $param
     * @return bool
     */
    public function hasNonEmptyParam($param)
    {
        return !empty($this->getParam($param, ''));
    }

    /**
     * @param $param
     * @return bool
     */
    public function hasEmptyParam($param)
    {
        return empty($this->getParam($param, ''));
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasPostParam($key)
    {
        return has_key($key, $this->_POST);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasGetParam($key)
    {
        return has_key($key, $this->_GET);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRequestParam($key)
    {
        return has_key($key, $this->_REQUEST);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasCookieParam($key)
    {
        return has_key($key, $this->_COOKIE);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRawPostParam($key)
    {
        return has_key($key, $this->_RAW_POST);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRawGetParam($key)
    {
        return has_key($key, $this->_RAW_GET);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRawCookieParam($key)
    {
        return has_key($key, $this->_RAW_COOKIE);
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function hasParams(array $keys)
    {
        foreach ($keys as $key)
        {
            if ($this->isMissingParam($key))
                return false;
        }

        return true;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function isMissingParams(array $keys)
    {
        return  !$this->hasParams($keys);
    }

    /**
     * @return array|false|QString
     */
    public function getIp()
    {
        if (empty($this->_request_ip))
        {
            if (getenv('HTTP_CLIENT_IP'))
                $address = getenv('HTTP_CLIENT_IP');
            else if(getenv('HTTP_X_FORWARDED_FOR'))
                $address = getenv('HTTP_X_FORWARDED_FOR');
            else if(getenv('HTTP_X_FORWARDED'))
                $address = getenv('HTTP_X_FORWARDED');
            else if(getenv('HTTP_FORWARDED_FOR'))
                $address = getenv('HTTP_FORWARDED_FOR');
            else if(getenv('HTTP_FORWARDED'))
                $address = getenv('HTTP_FORWARDED');
            else if(getenv('REMOTE_ADDR'))
                $address = getenv('REMOTE_ADDR');
            else
                $address = 'UNKNOWN';

            $this->_request_ip = $address;
        }

        return $this->_request_ip;
    }


    /**
     * @return QString
     */
    public function toLog()
    {
        return json_encode($this);
    }

    /**
     * @return bool
     */
    public static function isSSL()
    {
        if ( !empty( $_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            return true;

        if ( !empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
            return true;

        return false;
    }

    /**
     * @param bool $addSlashes
     * @return string
     */
    public static function getProtocol($addSlashes = false)
    {
        $txt = "http";

        if (self::isSSL())
            $txt .= "s";

        if ($addSlashes)
            $txt .= "://";

        return $txt;
    }

    /**
     * @return null
     */
    public static function getDomain()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
    }

    /**
     * @param bool $add_last_slash
     * @return string
     */
    public static function getDomainWithProtocol($add_last_slash = true)
    {
        $domain   = self::getDomain();
        $protocol = self::getProtocol(true);

        $url = $protocol.$domain;

        if ($add_last_slash)
            $url .= "/";

        return $url;
    }

    /**
     * @return int|QString
     */
    public static function getDomainOnly()
    {
        $u = self::getDomain();

        $x = explode(".", $u);

        $c = count($x);

        $a = $x[$c-2];
        $b = $x[$c-1];

        $c = $a .".". $b;

        return $c;
    }

    /**
     * @return string
     */
    public function getSchemeAndHttpHost()
    {
        $scheme = self::getProtocol();
        $port = self::getPort();
        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return self::getDomainWithProtocol(false);
        }

        return $scheme.'://'.self::getDomain().':'.$port;
    }

    /**
     * @return int|mixed
     */
    public function getPort()
    {
        if (!$host = $this->getHeader('HOST')) {
            return $_SERVER['SERVER_PORT'];
        }
        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }
        if (false !== $pos && $port = substr($host, $pos + 1)) {
            return (int) $port;
        }

        return 'https' === self::getProtocol() ? 443 : 80;
    }

    /**
     * @param $uri
     * @param bool $add_slash_to_base_url
     * @return QString
     */
    public static function genFullURLFromURI($uri, $add_slash_to_base_url = true)
    {
        return self::getDomainWithProtocol($add_slash_to_base_url).$uri;
    }


    /**
     * @param $param
     * @param $value
     * @return bool
     */
    public function isParamEqual($param, $value)
    {
        if ($this->hasParam($param))
        {
            if ($this->getParam($param) === $value)
                return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        Import::library("mobile_detect/Mobile_Detect.php");

        $detect = new \Mobile_Detect();

        return $detect->isMobile();
    }


    /**
     * @return bool
     */
    public function isCommandLine()
    {
        return (PHP_SAPI === 'cli');
    }

    /**
     * @return QString
     */
    public static function getUriWithQueryString()
    {
        return htmlspecialchars($_SERVER["REQUEST_URI"]);
    }


    /**
     * @return  string
     */
    public static function getUri()
    {
        $uri = urldecode(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );

       return $uri;
    }

    /**
     * @return string
     */
    public function getUriWithoutQueryString()
    {
        $uri = urldecode(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );

        return $uri;
    }


    /**
     * @return int
     */
    public function getNumPostParams()
    {
        if (is_array($this->_POST))
            return count($this->_POST) ;

        return 0;
    }

    /**
     * @return mixed
     */
    public function getPostParams()
    {
        return $this->_POST;
    }


    /**
     * @return \Quantum\BrowserDetector
     */
    public function getBrowser()
    {
        return BrowserDetector::getInstance();
    }


    /**
     * @return QString
     */
    public static function getPublicURL()
    {
        return Utilities::getFullUrl();
    }

    /**
     * @return bool|int
     */
    public static  function getNumberInURL()
    {
       return Security::sanitize_int((int)preg_replace("/[^0-9]{1,4}/", '', self::getPublicURL()));
    }

    /**
     * @param bool $key
     * @return bool|mixed|ValueTree
     */
    public  function server($key = false)
    {
        if (!isset($this->_server_tree))
            $this->_server_tree = new ValueTree($_SERVER, true, true);

        if ($key)
            return $this->_server_tree->get($key);

        return $this->_server_tree;
    }

    /**
     * @param bool $key
     * @return bool|mixed|ValueTree
     */
    public  function environment($key = false)
    {
        if (!isset($this->_env_tree))
            $this->_env_tree = new ValueTree($_ENV, true, true);

        if ($key)
            return $this->_env_tree->get($key);

        return $this->_env_tree;
    }

    /**
     * @return array|false|QString
     */
    public static function getHeaders()
    {
        $headers = '';

        if (function_exists("apache_request_headers"))
        {
            $headers = apache_request_headers();
        }
        else
        {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    public static function getHeadersAsString()
    {
        $headers = self::getHeaders();

        $txt = "";

        foreach ($headers as $header => $value) {
            $txt .= "$header: $value \n";
        }

        return $txt;
    }

    /**
     * @param bool $key
     * @return bool|mixed|ValueTree
     */
    public function header($key = false)
    {
        if (!isset($this->_headers_tree))
            $this->_headers_tree = new ValueTree(self::getHeaders(), true, true);

        if ($key)
            return $this->_headers_tree->get($key);

        return $this->_headers_tree;
    }

    /**
     * Find a header case insensitive
     * @param $key
     * @return bool
     */
    public function hasHeaderIgnoreCase($key)
    {
        $headers = new_vt(self::getHeaders());
        $headers->changeKeysToLowerCase();

        return $headers->has(qs($key)->toLowerCase()->toStdString());
    }


    /**
     * @param $key
     * @return bool|mixed
     */
    public function getHeaderIgnoreCase($key)
    {
        $headers = new_vt(self::getHeaders());
        $headers->changeKeysToLowerCase();

        return $headers->get(qs($key)->toLowerCase()->toStdString(), null);
    }

    public function getHeader($key, $fallback = false)
    {
        if (!isset($this->normalized_headers))
        {
            $headers = new_vt(self::getHeaders());

            $headers->changeKeysToLowerCase();

            $this->normalized_headers = $headers;
        }

        $key = qs($key)->toLowerCase()->toStdString();

        return $this->normalized_headers->get($key, $fallback);
    }

    public function hasHeader($key)
    {
        if (!isset($this->normalized_headers))
        {
            $headers = new_vt(self::getHeaders());

            $headers->changeKeysToLowerCase();

            $this->normalized_headers = $headers;
        }

        $key = qs($key)->toLowerCase()->toStdString();

        return $this->normalized_headers->has($key);
    }

    /**
     * @return bool
     */
    public function isJson()
    {
        return Utilities::stringContainsIgnoreCase($this->header('CONTENT_TYPE'), ['/json', '+json']);
    }

    /**
     * @return array
     */
    public function segments()
    {
        $segments = explode('/', $this->path());
        return array_values(array_filter($segments, function($v) { return $v != ''; }));
    }

    /**
     * @param $index
     * @param null $default
     * @return mixed
     */
    public function segment($index, $default = null)
    {
        return array_get($this->segments(), $index - 1, $default);
    }

    /**
     * @return QString
     */
    public function path()
    {
        $pattern = trim($this->getUriWithQueryString(), '/');
        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setRequestParam($key, $value)
    {
        $this->_REQUEST[$key] = $value;
        $_REQUEST = $this->_REQUEST;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setPostParam($key, $value)
    {
        $this->_POST[$key] = $value;
        $_POST = $this->_POST;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setGetParam($key, $value)
    {
        $this->_GET[$key] = $value;
        //$_GET = $this->_GET;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setCookieParam($key, $value)
    {
        $this->_COOKIE[$key] = $value;
        //$_COOKIE = $this->_COOKIE;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setServerParam($key, $value)
    {
        $this->_SERVER[$key] = $value;
        //$_SERVER = $this->_SERVER;
    }

    /**
     * @return mixed
     */
    public function cookies()
    {
        return Cookies::getInstance();
    }

    /**
     * @param $ips
     * @return bool
     */
    public function isFromIp($ips)
    {
        if (empty($ips))
            return false;

        $client_ip = $this->getIp();

        if (is_string($ips))
            return $client_ip === $ips;

        if (is_array($ips))
        {
            foreach ($ips as $ip)
            {
                if ($client_ip === $ip)
                    return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLocalHost()
    {
        $ips = $this->getLocalHostIps();

        return $this->isFromIp($ips);

    }

    /**
     * @return array
     */
    public static function getLocalHostIps()
    {
        $ips = ["::1", "127.0.0.1"];
        return $ips;
    }

    /**
     * @return bool|QString
     */
    public function getVisitorCountryCode()
    {
        if (empty($this->visitor_country_code))
        {
            if (Session::hasParam("visitor_country_code"))
            {
                $this->visitor_country_code = Session::get("visitor_country_code");
            }
            else
            {
                $cf_country_code = $this->getHeader('CF-IPCountry');

                if ($cf_country_code != false && $cf_country_code != "XX")
                {
                    $this->visitor_country_code = $cf_country_code;
                }
                else
                {
                    $this->visitor_country_code = MaxmindGeoIp::getCountryCode($this->getIp());
                }

                Session::set("visitor_country_code", $this->visitor_country_code);
            }
        }

        return $this->visitor_country_code;
    }

    /**
     * @return bool|QString
     */
    public function getVisitorCountry()
    {
        if (empty($this->visitor_country))
        {
            if (Session::hasParam("visitor_country"))
            {
                $this->visitor_country = Session::get("visitor_country");
            }
            else
            {
                $cf_country_code = $this->getHeader('CF-IPCountry');

                if ($cf_country_code != false && $cf_country_code != "XX")
                {
                    $country = \Country::find_by_code($cf_country_code);

                    if (!empty($country)) {
                        $this->visitor_country = $country->name;
                    }
                    else {
                        $this->visitor_country = "UNKNOWN";
                    }
                }
                else
                {
                    $this->visitor_country = MaxmindGeoIp::getCountry($this->getIp());
                }

                Session::set("visitor_country", $this->visitor_country);
            }

        }

        return $this->visitor_country;
    }

    /**
     * @param $target
     */
    public function redirect($target)
    {
        redirect_to($target);
    }

    /**
     * @param $rules
     * @return bool
     */
    public function validatePost($rules)
    {
        $validator = new RequestParamValidator();
        $success = $validator->post($rules);
        $this->validation_errors_post = $validator->getErrors();
        return $success;
    }

    /**
     * @param $rules
     * @return bool
     */
    public function validateGet($rules)
    {
        $validator = new RequestParamValidator();
        $success = $validator->get($rules);
        $this->validation_errors_get = $validator->getErrors();
        return $success;
    }

    /**
     * @return mixed
     */
    public function getValidationErrorsForGet()
    {
        return $this->validation_errors_get;
    }

    /**
     * @return mixed
     */
    public function getValidationErrorsForPost()
    {
        return $this->validation_errors_post;
    }

    /**
     * @param $rules
     */
    public function validateGetRulesOrDie($rules)
    {
        if (!$this->validateGet($rules))
        {
            ApiException::custom('invalid_request', '500', json_encode($this->getValidationErrorsForGet()));
        }
    }

    /**
     * @param $rules
     */
    public function validatePostRulesOrDie($rules)
    {
        if (!$this->validatePost($rules))
        {
            ApiException::custom('invalid_request', '500', json_encode($this->getValidationErrorsForPost()));
        }
    }

    /**
     * @return bool|mixed|QString
     */
    public function getId()
    {
        if (empty($this->current_id))
        {
            $this->current_id = $this->findId();
        }

        return $this->current_id;
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        $id = qs($this->getId());

        return ($id->isNumber() || $id->isUuid());
    }

    /**
     * @return bool|mixed|QString
     */
    public function findId()
    {
        $segments = $this->segments();

        foreach ($segments as $index => $segment)
        {
            if (qs($segment)->contains('?'))
            {
                $segment = qs($segment)->upToFirstOccurrenceOf('?')->toStdString();
            }

            if (qs($segment)->isUuid())
            {
                return $segment;
            }

            if (qs($segment)->isNumber())
            {
                return $segment;
            }
        }

        return false;
    }

    /**
     *
     */
    public function redirectToSameUriWithoutQueryString()
    {
        $this->redirect($this->getUriWithoutQueryString());
    }

    /**
     * @return mixed|string
     */
    public function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown";
    }

    /**
     * @param $filename
     * @return bool
     */
    public function hasUploadedFile($filename)
    {
        return is_uploaded_file($_FILES[$filename]['tmp_name']);
    }

    /**
     * @return Psr7\ServerRequest
     */
    public function toPsr7()
    {
        return ServerRequestFactory::fromGlobals();
    }

    public function hasUploads()
    {
        return !empty($_FILES);
    }


    public function getVisitorFingerprint()
    {
        if (!isset($this->visitor_fingerprint))
        {
            $data = new_vt();
            $data->set('visitor_ip', $this->getIp());
            $data->setProperties((array)$this->getBrowser());

            $this->visitor_fingerprint = $data->getHash();
        }

        return $this->visitor_fingerprint;
    }



}