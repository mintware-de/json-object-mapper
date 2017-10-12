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
 * This class represents the DateTimeField Annotation
 *
 * @Annotation
 */
class DateTimeField extends JsonField
{
    /**
     * The target format
     *
     * @var string
     */
    public $format = null;
}
