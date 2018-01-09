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

class InvalidJsonException extends \Exception
{
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct('The JSON is not valid.', $code, $previous);
    }
}
