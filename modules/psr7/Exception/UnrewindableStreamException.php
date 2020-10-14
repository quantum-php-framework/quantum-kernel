<?php
/**
 * 
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * 
 */

declare(strict_types=1);

namespace Quantum\Psr7\Exception;

use RuntimeException;

class UnrewindableStreamException extends RuntimeException implements ExceptionInterface
{
    public static function forCallbackStream() : self
    {
        return new self('Callback streams cannot rewind position');
    }
}
