<?php


namespace Quantum\Qubit;


class QubitRequest
{
    public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $this->params = new_vt($request->getQueryParams());
    }

    public function isValid()
    {
        if ($this->params->has('command') &&
            $this->params->has('app') &&
            $this->params->has('key'))
        {
            return true;
        }

        return false;
    }

    public function getParam($key)
    {
        return $this->params->get($key);
    }

    public function getCommand()
    {
        return $this->params->get('command');
    }

    public function getApp()
    {
        return $this->params->get('app');
    }

    public function getKey()
    {
        return $this->params->get('key');
    }

    public function getTask()
    {
        return $this->params->get('task');
    }

    public function getData()
    {
        return $this->params->get('data');
    }

}