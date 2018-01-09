<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
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
class ClassNotFoundException extends \Exception
{
    public function __construct($className, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('The class %s was not found.', $className);
        parent::__construct($message, $code, $previous);
    }
}
