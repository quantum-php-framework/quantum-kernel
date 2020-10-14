<?php

namespace Quantum\Middleware;

use  Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

/**
 * Class PostTooLargeException
 * @package Quantum\Middleware
 */
class PostTooLargeException extends \Quantum\HttpException
{
    /**
     * PostTooLargeException constructor.
     *
     * @param  string|null  $message
     * @param  \Exception|null  $previous
     * @param  array  $headers
     * @param  int  $code
     * @return void
     */
    public function __construct($message = null, Exception $previous = null, array $headers = array())
    {
        parent::__construct(413, $message, $headers);
    }
}

/**
 * Class ValidatePostSize
 * @package Quantum\Middleware
 */
class ValidatePostSize extends Foundation\SystemMiddleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws PostTooLargeException
     */
    public function handle(Request $request, Closure $next)
    {
        $max = $this->getPostMaxSize();

        if ($max > 0 && $request->server('CONTENT_LENGTH') > $max)
        {
            $this->logException('max_post_size');
            throw new PostTooLargeException;
        }

        return $next($request);
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    protected function getPostMaxSize()
    {

        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));
        $postMaxSize = (int) $postMaxSize;

        switch ($metric) {
            case 'K':
                return $postMaxSize * 1024;
            case 'M':
                return $postMaxSize * 1048576;
            case 'G':
                return $postMaxSize * 1073741824;
            default:
                return $postMaxSize;
        }
    }
}