<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM\Objects;

use MintWare\JOM\JsonField;

class Autobot
{
    /**
     * @var \DateTime
     * @JsonField(name="created_at", transformer="\MintWare\Tests\JOM\Transformer\MicrotimeTransformer")
     */
    public $createdAt;

    /**
     * @var string[]
     * @JsonField(name="tags", type="string", postTransformer="\MintWare\Tests\JOM\Transformer\TagTransformer")
     */
    public $tags;

    /**
     * @var
     * @JsonField(name="series", type="string", preTransformer="\MintWare\Tests\JOM\Transformer\PaddingTransformer")
     */
    public $series;
}
