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

/**
 * Data holder for tests
 *
 * @package MintWare\Tests
 */
class SimplePerson
{
    /** @JsonField */
    public $firstname;

    /** @JsonField */
    public $surname;

    /** @JsonField */
    public $age;

    /** @JsonField */
    public $height;

    /** @JsonField */
    public $is_cool;

    /** @JsonField */
    public $nicknames;

    /** @JsonField */
    public $dictionary;

    /** @JsonField */
    public $address;
}
