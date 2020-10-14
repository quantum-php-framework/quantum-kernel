<?php

namespace Quantum\Streams;

use Quantum\File;
use Quantum\Result;
use Quantum\URL;

/**
 * Class FileDownloadStream
 * @package Quantum\Streams
 */
class FileDownloadStream
{

    public function __construct(URL $url, File $dest)
    {
        $this->url = $url;
        $this->target = $dest;
    }

    public function download()
    {
        $options = array(
            CURLOPT_FILE => is_resource($this->target) ? $this->target : fopen($this->target, 'w'),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $this->url,
            CURLOPT_FAILONERROR => true, // HTTP code > 400 will throw curl error
        );

        if ($this->url->hasPostData())
        {
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';
            $options[CURLOPT_POSTFIELDS] = $this->url->getPostData();
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $return = curl_exec($ch);

        if ($return === false)
        {
            $error = curl_error($ch);

            return Result::fail($error);
        }
        else
        {
            return Result::ok();
        }
    }


}