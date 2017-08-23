<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM;

/**
 * This class represents the JsonField Annotation
 *
 * @Annotation
 */
class JsonField
{
    /**
     * The name of the field in the JSON object
     *
     * @var string
     */
    public $name = null;

    /**
     * The type of the field
     *
     * @var string
     */
    public $type = null;
}
