<?php

namespace Quantum;

use Quantum\Streams\FileDownloadStream;
use Quantum\Streams\PingStream;
use Quantum\Streams\SimpleInputStream;

/**
 * Class URL
 * @package Quantum
 */
class URL
{
    /**
     * @var QString
     */
    private $url;
    /**
     * @var array
     */
    private $parameters;
    /**
     * @var mixed
     */
    private $postData;
    /**
     * @var array
     */
    private $info;

    /**
     * URL constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = QString::create ($url);
        $this->parameters = array();
        $this->postData = null;
        $this->init();
    }

    /**
     *
     */
    private function init()
    {
        if ($this->url->indexOf('?') > 0)
        {
            parse_str($this->url->fromFirstOccurrenceOf('?'), $this->parameters);
        }

        $this->url = $this->url->upToFirstOccurrenceOf('?', false);
        $this->info = parse_url($this->url);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }


    /**
     * @return string
     */
    public function toString($includeGetParameters = true)
    {
        if ($includeGetParameters && !empty($this->parameters))
            return $this->url->append($this->getQueryString())->toStdString();

        return $this->url->toStdString();
    }


    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->url->isEmpty();
    }

    /**
     * @return bool
     */
    public function isWellFormed()
    {
        return $this->url->isNotEmpty();
    }

    /**
     * @return bool
     */
    public function hasPostData()
    {
        return !empty($this->postData);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        if (isset($this->info['host']))
            return $this->info['host'];

        return '';
    }

    /**
     * @return string
     */
    public function getPort()
    {
        if (isset($this->info['port']))
            return $this->info['port'];

        return '';
    }

    /**
     * @return string
     */
    public function getUser()
    {
        if (isset($this->info['user']))
            return $this->info['user'];

        return '';
    }

    /**
     * @return string
     */
    public function getPass()
    {
        if (isset($this->info['pass']))
            return $this->info['pass'];

        return '';
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        if (isset($this->info['fragment']))
            return $this->info['fragment'];

        return '';
    }

    /**
     * @param bool $includeGetParameters
     * @return string
     */
    public function getSubPath($includeGetParameters = false)
    {
        if (isset($this->info['path']))
        {
            $u = $this->info['path'];

            if ($includeGetParameters)
                return $u . $this->getQueryString();

            return $u;
        }

        return '';

    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return '?' . http_build_query($this->parameters);
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        if (isset($this->info['scheme']))
            return $this->info['scheme'];

        return '';
    }

    /**
     * @return bool
     */
    public function isLocalFile()
    {
        return $this->getScheme() == 'file';
    }


    /**
     * @return File
     */
    public function getLocalFile()
    {
        return qf($this->toString());
    }

    /**
     * @param $key
     * @param $value
     * @return URL
     */
    public function withParameter ($key, $value)
    {
        $c = clone $this;
        $c->parameters[$key] = $value;
        return $c;
    }

    /**
     * @param $params
     * @return URL
     */
    public function withParameters ($params)
    {
        $c = clone $this;
        $c->parameters = array_merge($this->parameters, $params);
        return $c;
    }

    /**
     * @param $newUrl
     * @return URL
     */
    public function withNewDomainAndPath ($newUrl)
    {
        $c = clone $this;
        $c->url = QString::create ($newUrl);
        return $c;
    }

    /**
     * @param $newPath
     * @return URL
     */
    public function withNewSubPath ($newPath)
    {
        $c = clone $this;
        $c->url = $c->url->replace($this->getSubPath(), $newPath);
        return $c;
    }

    /**
     * Appends a path to the current url
     * @param $path
     * @return URL
     */
    public function withPath($path)
    {
        $c = clone $this;
        $c->url = $c->url->append($path);
        return $c;
    }

    /**
     * @param $newPort
     * @return URL
     */
    public function withNewPort ($newPort)
    {
        $c = clone $this;
        $c->url = $c->url->replace($this->getPort(), $newPort);
        return $c;
    }

    /**
     * @param $data
     * @return URL
     */
    public function withPostData($data)
    {
        $c = clone $this;
        $c->postData = $data;
        return $c;
    }

    /**
     * @param $key
     * @param $value
     * @return URL
     */
    public function withPostParameter ($key, $value)
    {
        $c = clone $this;
        $c->postData[$key] = $value;
        return $c;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getParameterNames()
    {
        return array_keys($this->parameters);
    }

    /**
     * @return array
     */
    public function getParameterValues()
    {
        return array_values($this->parameters);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getParameter($key)
    {
        if (in_array($key, $this->parameters))
            return $this->parameters[$key];

        return null;
    }

    /**
     * @param int $timeOut
     * @param int $connectTimeOut
     * @return CurlRequest
     * @throws \Exception
     */
    public function createCurlRequest($timeOut = 30, $connectTimeOut = 30)
    {
        $r = new CurlRequest($this->toString());
        $r->setTimeout($timeOut);
        $r->setConnectTimeout($connectTimeOut);

        return $r;
    }

    /**
     * @return SimpleInputStream
     */
    public function createSimpleInputStream()
    {
        return new SimpleInputStream($this);
    }

    /**
     * @param File $destination
     * @return FileDownloadStream
     */
    public function createFileDownloadStream(File $destination)
    {
        return new FileDownloadStream($this, $destination);
    }

    /**
     * @param File $destination
     * @return bool|string
     */
    public function downloadToFile(File $destination)
    {
        $stream = $this->createFileDownloadStream($destination);
        return $stream->download();
    }

    /**
     * @return bool|string
     */
    public function readStream()
    {
        $stream = $this->createSimpleInputStream();
        return $stream->read();
    }

    /**
     * @return false|string
     */
    public function getContents($usePostCommand = true)
    {
        $contextConfig = array('http' =>
            array(
                'method'  => !empty($usePostCommand) ? 'POST': 'GET',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $this->getQueryString()
            )
        );

        return file_get_contents($this, false, stream_context_create($contextConfig));
    }

    /**
     * @param bool $usePostCommand
     * @param int $timeOut
     * @param int $connectTimeOut
     * @return string
     * @throws \Exception
     */
    public function readEntireTextStream($usePostCommand = false, $timeOut = 30, $connectTimeOut = 30)
    {
        $r = $this->createCurlRequest($timeOut, $connectTimeOut);

        if ($usePostCommand)
        {
            $r->setPostFields($this->postData);
            $r->setRequestType('POST');
        }

        $r->execute();

        return $r->getResponse();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function ping()
    {
        $ping = new PingStream($this);
        return $ping->ping();
    }

    /**
     * @param $possibleUrl
     * @return bool
     */
    public static function isProbablyAWebsiteURL($possibleUrl)
    {
        $possibleUrl = qs($possibleUrl);

        $protocols = ["http:", "https:", "ftp:"];

        foreach ($protocols as $protocol)
        {
            if ($possibleUrl->startsWithIgnoreCase($protocol))
                return true;
        }

        if ($possibleUrl->containsAnyOf(['@', ' ']))
            return false;

        $topLevelDomain = $possibleUrl->upToFirstOccurrenceOf('/', false, false)
            ->fromLastOccurrenceOf('.', cdfalse, false);

        return $topLevelDomain->isNotEmpty() && $topLevelDomain->length() <= 3;
    }

    /**
     * @param $possibleEmailAddress
     * @return bool
     */
    public static function isProbablyAnEmailAddress ($possibleEmailAddress)
    {
        $possibleEmailAddress = qs($possibleEmailAddress);
        $atSign = $possibleEmailAddress->indexOf('@');

        return $atSign > 0
            && $possibleEmailAddress->lastIndexOf('.') > ($atSign + 1)
            && !$possibleEmailAddress->endsWith('.');
    }





}