<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - now by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM;

/**
 * A simple dataholder for tests
 *
 * @package MintWare\Tests
 */
class FailPerson
{
    /** @MintWare\JOM\JsonField(name="foo", type="integer") */
    protected $name;
}
