<?php

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
