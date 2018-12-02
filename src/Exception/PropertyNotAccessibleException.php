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
 * If a property is not accessible, you can throw this exception
 *
 * @package MintWare\JOM\Exception
 */
class PropertyNotAccessibleException extends \Exception
{
    public function __construct($propertyName, $code = 0, \Throwable $previous = null)
    {
        $uc = ucwords($propertyName);
        $setters = sprintf('"%s"', implode($uc . '", "', ['set', 'add']) . $uc);
        $message = sprintf('Neither the property "%s" nor one of the methods %s (or getter) have public access.', $propertyName, $setters);
        parent::__construct($message, $code, $previous);
    }
}
