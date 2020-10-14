<?php

namespace Quantum;

/**
 * Represents the 'success' or 'failure' of an operation,
 * and holds an associated error message to describe the error when there's a failure.
 * Class Result
 * @package Quantum
 *
 */
class Result
{
    /**
     * @var
     */
    private $success;

    /**
     * @var
     */
    public $data;

    /**
     * Result constructor.
     * @param $success
     * @param string $message
     */
    public function __construct($success, $message = "", $data = null)
    {
        $this->success = $success;
        $this->data = $data;

        if (!empty($message))
        {
            $this->message = $message;
        }
        else
        {
            if (!$this->success)
                $this->message = 'Unknown Error';
        }

    }


    public static function ok($message = '', $data = null)
    {
        $r = new Result(true, $message, $data);

        return $r;
    }


    public static function fail($message = '', $data = null)
    {
        $r = new Result(false, $message, $data);

        return $r;
    }

    /**
     * Returns true if this result indicates a success.
     * @return bool
     */
    public function wasOk()
    {
        return $this->success === true;
    }

    /**
     * Returns true if this result indicates a failure.
     * @return bool
     */
    public function failed()
    {
        return $this->success === false;
    }

    /**
     * Returns the error message that was set when this result was created.
     * For a successful result, this may be an empty string;
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->message;
    }

    /**
     * Returns the data that was set when this result was created
     * this may be null;
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

}