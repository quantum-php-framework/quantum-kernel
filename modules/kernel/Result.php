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
     * Result constructor.
     * @param $success
     * @param string $message
     */
    public function __construct($success, $message = "")
    {
        $this->success = $success;

        if (!$this->success)
        {
            if (!empty($message))
                $this->message = $message;
            else
                $this->message = "Unknown Error";
        }
    }

    /**
     * Creates and returns a 'successful' result.
     * @return Result
     */
    public static function ok()
    {
        $r = new Result(true);

        return $r;
    }

    /**
     * Creates a 'failure' result.
     * If you pass a blank error message in here, a default "Unknown Error" message will be used instead.
     * @param string $message
     * @return Result
     */
    public static function fail($message = "")
    {
        $r = new Result(false, $message);

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
     * For a successful result, this will be an empty string;
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->message;
    }

}