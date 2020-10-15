<?php

declare(strict_types=1);

namespace Quantum\Psr7Emitter\Exception;

use RuntimeException;

class EmitterException extends RuntimeException implements \Quantum\Psr7\Exception\ExceptionInterface
{
    public static function forHeadersSent()
    {
        return new self('Unable to emit response; headers already sent');
    }

    public static function forOutputSent()
    {
        return new self('Output has been emitted previously; cannot emit response');
    }
}
