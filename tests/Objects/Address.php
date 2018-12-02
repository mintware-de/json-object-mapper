<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - now by Julian Finkler <julian@mintware.de>
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
class Address
{
    /** @MintWare\JOM\JsonField(name="street", type="string") */
    public $street;

    /** @MintWare\JOM\JsonField(name="zip_code", type="string") */
    public $zipCode;

    /** @MintWare\JOM\JsonField(name="town", type="string") */
    public $town;

    /** @MintWare\JOM\JsonField(name="country", type="string") */
    public $country;
}
