<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM\Serializer;

use MintWare\JOM\Exception\InvalidJsonException;

class JsonSerializer implements SerializerInterface
{
    /** @inheritdoc */
    function deserialize($json)
    {
        if (!is_array($data = json_decode($json, true))) {
            throw new InvalidJsonException();
        }
        return $data;
    }
}