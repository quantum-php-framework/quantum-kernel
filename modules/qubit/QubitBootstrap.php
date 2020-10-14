<?php

namespace Quantum\Qubit;

/**
 * Class QubitBootstrap
 */
class QubitBootstrap
{
    private $qubit;
    /**
     * @var bool
     */
    private $paused;

    /**
     * @var React\EventLoop
     */
    private $loop;

    /**
     * QuantumCronScheduler constructor.
     */
    public function __construct()
    {
        $this->paused = 0;

        \Quantum\Autoloader::getInstance();

        cli_echo('Welcome to Qubit!');
    }


    public function initLoop($initServer = true)
    {
        $this->loop = $loop = \React\EventLoop\Factory::create();

        $qubit = new Qubit();

        if ($initServer)
        {
            $api_port = Qubit::getConfig()->api_port;

            $server = new \React\Http\Server($loop, function (\Psr\Http\Message\ServerRequestInterface $request ) use (&$qubit) {
                $response = $qubit->process($request);

                return new \React\Http\Message\Response(
                    200,
                    array(
                        'Content-Type' => 'text/plain'
                    ),
                    $response
                );
            });

            $socket = new \React\Socket\Server($api_port, $loop);
            $server->listen($socket);

            $server->on('error', function (\Exception $e) {
                cli_echo('Error: ' . $e->getMessage());
            });

            cli_echo('Listening on port: '.$api_port);
        }


        $loop->addPeriodicTimer(10, function () use (&$qubit){
            if (!$this->isPaused())
                $qubit->workqueue();
        });

        $loop->addSignal(SIGINT, $func = function ($signal) use ($loop, &$func) {
            cli_echo('Shutting down...');
            $loop->stop();
        });

        $loop->addSignal(SIGUSR1, $func = function ($signal) use ($loop, &$func) {
            $this->restart();
        });

        $loop->addSignal(SIGUSR2, $func = function ($signal) use ($loop, &$func) {
            $this->pause();
        });

        $loop->addSignal(SIGCONT, $func = function ($signal) use ($loop, &$func) {
            $this->continue();
        });

        $loop->run();
    }


    private function restart()
    {
        cli_echo('restart');
        $this->initLoop();
    }

    private function pause()
    {
        cli_echo('paused');
        $this->paused = true;
    }

    private function continue()
    {
        cli_echo('continuing');
        $this->paused = false;
    }

    private function terminate()
    {
        $this->loop->stop();
        cli_echo('shutting down');
        exit();
    }

    private function isPaused()
    {
        return $this->paused === true;
    }
}