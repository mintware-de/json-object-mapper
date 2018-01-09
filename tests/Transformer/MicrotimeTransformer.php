<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM\Transformer;

use MintWare\JOM\TransformerInterface;

class MicrotimeTransformer implements TransformerInterface
{
    /** {@inheritdoc} */
    public static function transform($data)
    {
        if ($data === null) {
            return null;
        }

        $parts = explode('.', $data, 2);
        $dt = new \DateTime();
        $dt->setTimestamp($parts[0]);
        return $dt;
    }

    /** {@inheritdoc} */
    public static function reverseTransform($data)
    {
        return $data->getTimestamp() . '000';
    }
}
