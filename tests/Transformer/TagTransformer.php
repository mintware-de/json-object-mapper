<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - now by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM\Transformer;

use MintWare\JOM\TransformerInterface;

/**
 * Demo Post Transformer
 * Transforms a comma separated string into array and reverse
 *
 * @package MintWare\Tests\JOM\Transformer
 */
class TagTransformer implements TransformerInterface
{
    /** {@inheritdoc} */
    public static function transform($data)
    {
        return array_filter(array_map('trim', explode(',', $data)));
    }

    /** {@inheritdoc} */
    public static function reverseTransform($data)
    {
        return implode(',', array_filter(array_map('trim', $data)));
    }
}
