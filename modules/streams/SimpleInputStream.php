<?php

namespace Quantum\Streams;

use Quantum\URL;

/**
 * Class InputStream
 * @package Quantum\Streams
 */
class SimpleInputStream
{

    public function __construct(URL $url)
    {
        $this->url = $url;
    }

    public function read()
    {
        $handle = fopen($this->url, "r");

        $this->response = stream_get_contents($handle);

        fclose($handle);

        return $this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }




}