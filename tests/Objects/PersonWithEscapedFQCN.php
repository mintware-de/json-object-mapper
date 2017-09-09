<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM\Objects;

/**
 * Data holder for tests
 *
 * @package MintWare\Tests
 */
class PersonWithEscapedFQCN
{
    /** @MintWare\JOM\JsonField(name="addresses", type="MintWare\\Tests\\JOM\\Objects\\Address[]") */
    public $addresses;
}
