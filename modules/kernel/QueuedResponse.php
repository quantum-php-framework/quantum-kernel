<?php

namespace Quantum;

/**
 * Class QueuedResponse
 * @package Quantum
 */
class QueuedResponse
{
    public $response;

    public $is_view;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function isView()
    {
        return $this->is_view;
    }

    /**
     * @param mixed $is_view
     */
    public function setIsView($is_view): void
    {
        $this->is_view = $is_view;
    }


    

    
    
    
   
    
    
    
}