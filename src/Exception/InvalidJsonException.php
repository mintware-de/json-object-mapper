<?php
/**
 * Created by PhpStorm.
 * User: Julian
 * Date: 09.09.2017
 * Time: 08:20
 */

namespace MintWare\JOM\Exception;

class InvalidJsonException extends \Exception
{
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct('The JSON is not valid.', $code, $previous);
    }
}
