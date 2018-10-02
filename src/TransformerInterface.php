<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM;

interface TransformerInterface
{
    /**
     * Transform the data
     *
     * @param mixed $data The data
     * @return mixed The transformed data
     */
    public static function transform($data);

    /**
     * Reverse transform the data
     *
     * @param mixed $data The transformed data
     * @return mixed The reverse transformed data
     */
    public static function reverseTransform($data);
}
