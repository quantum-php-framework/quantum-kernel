<?php

namespace Quantum\Psr7;

use Psr\Http\Message\UploadedFileInterface;

use function preg_match_all;
use function urldecode;
use function array_key_exists;
use function is_string;
use function strpos;
use function strtolower;
use function strtr;
use function substr;
use function preg_match;
use function array_change_key_case;
use function explode;
use function implode;
use function is_array;
use function ltrim;
use function preg_replace;
use function strlen;


class Psr7Functions
{

    /**
     * Create an uploaded file instance from an array of values.
     *
     * @param array $spec A single $_FILES entry.
     * @throws Exception\InvalidArgumentException if one or more of the tmp_name,
     *     size, or error keys are missing from $spec.
     */
    public static function createUploadedFile(array $spec) : UploadedFile
    {
        if (! isset($spec['tmp_name'])
            || ! isset($spec['size'])
            || ! isset($spec['error'])
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$spec provided to %s MUST contain each of the keys "tmp_name",'
                . ' "size", and "error"; one or more were missing',
                __FUNCTION__
            ));
        }

        return new UploadedFile(
            $spec['tmp_name'],
            $spec['size'],
            $spec['error'],
            $spec['name'] ?? null,
            $spec['type'] ?? null
        );
    }


    /**
     * @param array $server Values obtained from the SAPI (generally `$_SERVER`).
     * @return array Header/value pairs
     */
    public static function marshalHeadersFromSapi(array $server) : array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if ($value === '') {
                continue;
            }

            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }

            if (strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }

            if (strpos($key, 'CONTENT_') === 0) {
                $name = strtr(strtolower($key), '_', '-');
                $headers[$name] = $value;
                continue;
            }
        }

        return $headers;
    }


    /**
     * Retrieve the request method from the SAPI parameters.
     */
    public static function marshalMethodFromSapi(array $server) : string
    {
        return $server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Return HTTP protocol version (X.Y) as discovered within a `$_SERVER` array.
     *
     * @throws Exception\UnrecognizedProtocolVersionException if the
     *     $server['SERVER_PROTOCOL'] value is malformed.
     */
    public static function marshalProtocolVersionFromSapi(array $server) : string
    {
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw Exception\UnrecognizedProtocolVersionException::forVersion(
                (string) $server['SERVER_PROTOCOL']
            );
        }

        return $matches['version'];
    }


    /**
     * Marshal a Uri instance based on the values presnt in the $_SERVER array and headers.
     *
     * @param array $server SAPI parameters
     * @param array $headers HTTP request headers
     */
    public static function marshalUriFromSapi(array $server, array $headers) : Uri
    {
        /**
         * Retrieve a header value from an array of headers using a case-insensitive lookup.
         *
         * @param array $headers Key/value header pairs
         * @param mixed $default Default value to return if header not found
         * @return mixed
         */
        $getHeaderFromArray = function (string $name, array $headers, $default = null) {
            $header  = strtolower($name);
            $headers = array_change_key_case($headers, CASE_LOWER);
            if (array_key_exists($header, $headers)) {
                $value = is_array($headers[$header]) ? implode(', ', $headers[$header]) : $headers[$header];
                return $value;
            }

            return $default;
        };

        /**
         * Marshal the host and port from HTTP headers and/or the PHP environment.
         *
         * @return array Array of two items, host and port, in that order (can be
         *     passed to a list() operation).
         */
        $marshalHostAndPort = function (array $headers, array $server) use ($getHeaderFromArray) : array {
            /**
             * @param string|array $host
             * @return array Array of two items, host and port, in that order (can be
             *     passed to a list() operation).
             */
            $marshalHostAndPortFromHeader = function ($host) {
                if (is_array($host)) {
                    $host = implode(', ', $host);
                }

                $port = null;

                // works for regname, IPv4 & IPv6
                if (preg_match('|\:(\d+)$|', $host, $matches)) {
                    $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
                    $port = (int) $matches[1];
                }

                return [$host, $port];
            };

            /**
             * @return array Array of two items, host and port, in that order (can be
             *     passed to a list() operation).
             */
            $marshalIpv6HostAndPort = function (array $server, ?int $port) : array {
                $host = '[' . $server['SERVER_ADDR'] . ']';
                $port = $port ?: 80;
                if ($port . ']' === substr($host, strrpos($host, ':') + 1)) {
                    // The last digit of the IPv6-Address has been taken as port
                    // Unset the port so the default port can be used
                    $port = null;
                }
                return [$host, $port];
            };

            static $defaults = ['', null];

            $forwardedHost = $getHeaderFromArray('x-forwarded-host', $headers, false);
            if ($forwardedHost !== false) {
                return $marshalHostAndPortFromHeader($forwardedHost);
            }

            $host = $getHeaderFromArray('host', $headers, false);
            if ($host !== false) {
                return $marshalHostAndPortFromHeader($host);
            }

            if (! isset($server['SERVER_NAME'])) {
                return $defaults;
            }

            $host = $server['SERVER_NAME'];
            $port = isset($server['SERVER_PORT']) ? (int) $server['SERVER_PORT'] : null;

            if (! isset($server['SERVER_ADDR'])
                || ! preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)
            ) {
                return [$host, $port];
            }

            // Misinterpreted IPv6-Address
            // Reported for Safari on Windows
            return $marshalIpv6HostAndPort($server, $port);
        };

        /**
         * Detect the path for the request
         *
         * Looks at a variety of criteria in order to attempt to autodetect the base
         * request path, including:
         *
         * - IIS7 UrlRewrite environment
         * - REQUEST_URI
         * - ORIG_PATH_INFO
         *
         * From ZF2's Zend\Http\PhpEnvironment\Request class
         * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
         * @license   http://framework.zend.com/license/new-bsd New BSD License
         */
        $marshalRequestPath = function (array $server) : string {
            // IIS7 with URL Rewrite: make sure we get the unencoded url
            // (double slash problem).
            $iisUrlRewritten = $server['IIS_WasUrlRewritten'] ?? null;
            $unencodedUrl    = $server['UNENCODED_URL'] ?? '';
            if ('1' === $iisUrlRewritten && ! empty($unencodedUrl)) {
                return $unencodedUrl;
            }

            $requestUri = $server['REQUEST_URI'] ?? null;

            if ($requestUri !== null) {
                return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
            }

            $origPathInfo = $server['ORIG_PATH_INFO'] ?? null;
            if (empty($origPathInfo)) {
                return '/';
            }

            return $origPathInfo;
        };

        $uri = new Uri('');

        // URI scheme
        $scheme = 'http';
        $marshalHttpsValue = function ($https) : bool {
            if (is_bool($https)) {
                return $https;
            }

            if (! is_string($https)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'SAPI HTTPS value MUST be a string or boolean; received %s',
                    gettype($https)
                ));
            }

            return 'on' === strtolower($https);
        };
        if (array_key_exists('HTTPS', $server)) {
            $https = $marshalHttpsValue($server['HTTPS']);
        } elseif (array_key_exists('https', $server)) {
            $https = $marshalHttpsValue($server['https']);
        } else {
            $https = false;
        }

        if ($https
            || strtolower($getHeaderFromArray('x-forwarded-proto', $headers, '')) === 'https'
        ) {
            $scheme = 'https';
        }
        $uri = $uri->withScheme($scheme);

        // Set the host
        [$host, $port] = $marshalHostAndPort($headers, $server);
        if (! empty($host)) {
            $uri = $uri->withHost($host);
            if (! empty($port)) {
                $uri = $uri->withPort($port);
            }
        }

        // URI path
        $path = $marshalRequestPath($server);

        // Strip query string
        $path = explode('?', $path, 2)[0];

        // URI query
        $query = '';
        if (isset($server['QUERY_STRING'])) {
            $query = ltrim($server['QUERY_STRING'], '?');
        }

        // URI fragment
        $fragment = '';
        if (strpos($path, '#') !== false) {
            [$path, $fragment] = explode('#', $path, 2);
        }

        return $uri
            ->withPath($path)
            ->withFragment($fragment)
            ->withQuery($query);
    }

    /**
     * Marshal the $_SERVER array
     *
     * Pre-processes and returns the $_SERVER superglobal. In particularly, it
     * attempts to detect the Authorization header, which is often not aggregated
     * correctly under various SAPI/httpd combinations.
     *
     * @param null|callable $apacheRequestHeaderCallback Callback that can be used to
     *     retrieve Apache request headers. This defaults to
     *     `apache_request_headers` under the Apache mod_php.
     * @return array Either $server verbatim, or with an added HTTP_AUTHORIZATION header.
     */
    public static function normalizeServer(array $server, callable $apacheRequestHeaderCallback = null) : array
    {
        if (null === $apacheRequestHeaderCallback && is_callable('apache_request_headers')) {
            $apacheRequestHeaderCallback = 'apache_request_headers';
        }

        // If the HTTP_AUTHORIZATION value is already set, or the callback is not
        // callable, we return verbatim
        if (isset($server['HTTP_AUTHORIZATION'])
            || ! is_callable($apacheRequestHeaderCallback)
        ) {
            return $server;
        }

        $apacheRequestHeaders = $apacheRequestHeaderCallback();
        if (isset($apacheRequestHeaders['Authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['Authorization'];
            return $server;
        }

        if (isset($apacheRequestHeaders['authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['authorization'];
            return $server;
        }

        return $server;
    }

    /**
     * Normalize uploaded files
     *
     * Transforms each value into an UploadedFile instance, and ensures that nested
     * arrays are normalized.
     *
     * @return UploadedFileInterface[]
     * @throws Exception\InvalidArgumentException for unrecognized values
     */
    public static function normalizeUploadedFiles(array $files) : array
    {
        /**
         * Traverse a nested tree of uploaded file specifications.
         *
         * @param string[]|array[] $tmpNameTree
         * @param int[]|array[] $sizeTree
         * @param int[]|array[] $errorTree
         * @param string[]|array[]|null $nameTree
         * @param string[]|array[]|null $typeTree
         * @return UploadedFile[]|array[]
         */
        $recursiveNormalize = function (
            array $tmpNameTree,
            array $sizeTree,
            array $errorTree,
            array $nameTree = null,
            array $typeTree = null
        ) use (&$recursiveNormalize) : array {
            $normalized = [];
            foreach ($tmpNameTree as $key => $value) {
                if (is_array($value)) {
                    // Traverse
                    $normalized[$key] = $recursiveNormalize(
                        $tmpNameTree[$key],
                        $sizeTree[$key],
                        $errorTree[$key],
                        $nameTree[$key] ?? null,
                        $typeTree[$key] ?? null
                    );
                    continue;
                }
                $normalized[$key] = self::createUploadedFile([
                    'tmp_name' => $tmpNameTree[$key],
                    'size' => $sizeTree[$key],
                    'error' => $errorTree[$key],
                    'name' => $nameTree[$key] ?? null,
                    'type' => $typeTree[$key] ?? null,
                ]);
            }
            return $normalized;
        };

        /**
         * Normalize an array of file specifications.
         *
         * Loops through all nested files (as determined by receiving an array to the
         * `tmp_name` key of a `$_FILES` specification) and returns a normalized array
         * of UploadedFile instances.
         *
         * This function normalizes a `$_FILES` array representing a nested set of
         * uploaded files as produced by the php-fpm SAPI, CGI SAPI, or mod_php
         * SAPI.
         *
         * @param array $files
         * @return UploadedFile[]
         */
        $normalizeUploadedFileSpecification = function (array $files = []) use (&$recursiveNormalize) : array {
            if (! isset($files['tmp_name']) || ! is_array($files['tmp_name'])
                || ! isset($files['size']) || ! is_array($files['size'])
                || ! isset($files['error']) || ! is_array($files['error'])
            ) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '$files provided to %s MUST contain each of the keys "tmp_name",'
                    . ' "size", and "error", with each represented as an array;'
                    . ' one or more were missing or non-array values',
                    __FUNCTION__
                ));
            }

            return $recursiveNormalize(
                $files['tmp_name'],
                $files['size'],
                $files['error'],
                $files['name'] ?? null,
                $files['type'] ?? null
            );
        };

        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            }

            if (is_array($value) && isset($value['tmp_name']) && is_array($value['tmp_name'])) {
                $normalized[$key] = $normalizeUploadedFileSpecification($value);
                continue;
            }

            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFile($value);
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = self::normalizeUploadedFiles($value);
                continue;
            }

            throw new Exception\InvalidArgumentException('Invalid value in files specification');
        }
        return $normalized;
    }


    /**
     * Parse a cookie header according to RFC 6265.
     *
     * PHP will replace special characters in cookie names, which results in other cookies not being available due to
     * overwriting. Thus, the server request should take the cookies from the request header instead.
     *
     * @param string $cookieHeader A string cookie header value.
     * @return array key/value cookie pairs.
     */
    public static function parseCookieHeader($cookieHeader) : array
    {
        preg_match_all('(
        (?:^\\n?[ \t]*|;[ ])
        (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
        =
        (?P<DQUOTE>"?)
            (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
        (?P=DQUOTE)
        (?=\\n?[ \t]*$|;[ ])
    )x', $cookieHeader, $matches, PREG_SET_ORDER);

        $cookies = [];

        foreach ($matches as $match) {
            $cookies[$match['name']] = urldecode($match['value']);
        }

        return $cookies;
    }

}