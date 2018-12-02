<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - now by Julian Finkler <julian@mintware.de>
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
    /**
     * TypeMismatchException
     *
     * @param string $expectedType The expected type
     * @param string $givenType The given Type
     * @param string $propertyName The name of the Property
     */
    public function __construct($expectedType, $givenType, $propertyName)
    {
        $message = sprintf('Wrong Type. Expected %s got %s. Property name: %s', $expectedType, $givenType, $propertyName);
        parent::__construct($message, 0, null);
    }
}
