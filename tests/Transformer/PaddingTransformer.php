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

/**
 * Demo Pre Transformer
 * Transforms a left padded number into regular number and reverse
 *
 * @package MintWare\Tests\JOM\Transformer
 */
class PaddingTransformer implements TransformerInterface
{
    /** {@inheritdoc} */
    public static function transform($data)
    {
        return ltrim($data, '0');
    }

    /** {@inheritdoc} */
    public static function reverseTransform($data)
    {
        return str_pad($data, 6, '0', STR_PAD_LEFT);
    }
}
