<?php

namespace Quantum\Streams;

/**
 * Class CSVOutputStream
 * @package Quantum\Streams
 */
class CSVOutputStream
{
    /**
     * @var
     */
    private $data;
    /**
     * @var null
     */
    private  $headers;

    /**
     * CSVOutputStream constructor.
     * @param $data
     * @param null $headers
     */
    public function __construct($data, $headers = null)
    {
        if (is_vt($data))
            $data = $data->getArray();

        $this->data = $data;
        $this->headers = $headers;
    }


    /**
     * @param $filename
     * @param bool $rewind_data
     */
    public function pushAsFileToClient($filename, $rewind_data = true)
    {
        $csv = fopen('php://temp/maxmemory:'. (10*1024*1024), 'r+');

        if (!empty($this->headers))
        {
            fputcsv($csv, $this->headers);
        }

       foreach ($this->data as $datum)
       {
            fputcsv($csv, $datum);
       }

        if (Utilities::getExtension($filename) != "csv")
            $filename .= '.csv';

        if ($rewind_data)
            rewind($csv);

        $data = stream_get_contents($csv);

        Output::getInstance()->pushFileFromString($data, $filename, "text/csv");
    }

    /**
     * @param $data
     * @param $filename
     * @param null $headers
     * @param bool $rewind_data
     */
    public static function asFile($data, $filename, $headers = null, $rewind_data = true)
    {
        $stream = new CSVOutputStream($data, $headers);
        $stream->pushAsFileToClient($filename, $rewind_data);
    }

}