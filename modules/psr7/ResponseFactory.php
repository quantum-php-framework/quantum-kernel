<?php
/**
 * 
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * 
 */

declare(strict_types=1);

namespace Quantum\Psr7;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Quantum\Psr7\Response\EmptyResponse;
use Quantum\Psr7\Response\HtmlResponse;
use Quantum\Psr7\Response\JsonResponse;
use Quantum\Psr7\Response\RedirectResponse;
use Quantum\Psr7\Response\TextResponse;
use Quantum\Psr7\Response\XmlResponse;

/**
 * Class ResponseFactory
 * @package Quantum\Psr7
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
    {
        return (new Response())
            ->withStatus($code, $reasonPhrase);
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function createResponseWithContents($contents, $status = 200, array $headers = [])
    {
        $response = new Response('php://temp', $status, $headers);
        $response->getBody()->write($contents);
        return $response;
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return TextResponse
     */
    public static function text($contents, $status = 200, array $headers = [])
    {
        return new TextResponse($contents, $status, $headers);
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return HtmlResponse
     */
    public static function html($contents, $status = 200, array $headers = [])
    {
        return new HtmlResponse($contents, $status, $headers);
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return XmlResponse
     */
    public static function xml($contents, $status = 200, array $headers = [])
    {
        return new XmlResponse($contents, $status, $headers);
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @param int $encodingOptions
     * @return JsonResponse
     */
    public static function json($contents, $status = 200, array $headers = [], int $encodingOptions = JsonResponse::DEFAULT_JSON_FLAGS)
    {
        return new JsonResponse($contents, $status, $headers, $encodingOptions);
    }

    /**
     * @param int $status
     * @param array $headers
     * @return EmptyResponse
     */
    public static function empty($status = 200, array $headers = [])
    {
        return new EmptyResponse($status, $headers);
    }

    /**
     * @param $uri
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public static function redirect($uri, $status = 200, array $headers = [])
    {
        return new RedirectResponse($uri, $status, $headers);
    }

    /**
     * @param $contents
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function custom($contents, $status = 200, array $headers = [])
    {
        return self::createResponseWithContents($contents, $status, $headers);
    }


    /**
     * @param $data
     * @return EmptyResponse|HtmlResponse|JsonResponse|RedirectResponse|XmlResponse
     */
    public static function fromVariableData($data = '')
    {
        if (is_psr7_response($data))
        {
            return $data;
        }

        if (is_array($data) || is_object($data))
        {
            return self::json($data);
        }

        $data = qs($data);

        if ($data->isHtml())
        {
           return self::html($data->toStdString());
        }

        if ($data->isJson())
        {
            return self::json($data->decodeJson());
        }

        if ($data->isXml())
        {
            return self::xml($data->toStdString());
        }

        if ($data->isUrl())
        {
            return self::redirect($data->toStdString());
        }

        if (!$data->isEmpty())
        {
            return self::html($data->toStdString());
        }
        else
        {
            return self::empty();
        }
    }
}
