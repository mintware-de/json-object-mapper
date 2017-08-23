<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM\Exception;

/**
 * If a class does not exist, you can throw this exception
 *
 * @package MintWare\JOM\Exception
 */
class TypeMismatchException extends \Exception
{
    public function __construct($expectedType, $givenType, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Wrong Type. Expected %s got %s', $expectedType, $givenType);
        parent::__construct($message, $code, $previous);
    }
}
