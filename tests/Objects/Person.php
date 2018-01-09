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

/**
 * Data holder for tests
 *
 * @package MintWare\Tests
 */
class Person
{
    /** @MintWare\JOM\JsonField(name="firstname", type="string") */
    public $name;

    /** @MintWare\JOM\JsonField(name="surname", type="string") */
    public $surname;

    /** @MintWare\JOM\JsonField(name="age", type="int") */
    public $age;

    /** @MintWare\JOM\JsonField(name="height", type="float") */
    public $height;

    /** @MintWare\JOM\JsonField(name="is_cool", type="bool") */
    public $isCool;

    /** @MintWare\JOM\JsonField(name="nicknames", type="array") */
    public $nicknames;

    /** @MintWare\JOM\JsonField(name="dictionary", type="object") */
    public $dictionary;

    /** @MintWare\JOM\JsonField(name="address", type="string|MintWare\Tests\JOM\Objects\Address") */
    public $address;

    /**
     * @var \DateTime
     * @MintWare\JOM\JsonField(name="created", type="datetime")
     */
    public $created;

    /**
     * @var \DateTime
     * @MintWare\JOM\DateTimeField(name="updated", type="date", format="timestamp")
     */
    public $updated;

    /**
     * @var \DateTime
     * @MintWare\JOM\DateTimeField(name="deleted", type="datetime", format="timestamp")
     */
    public $deleted;

    /** @var int */
    public $unmappedField = 12 * 3;

    /** @MintWare\JOM\JsonField(name="protected", type="string") */
    protected $protectedProp;

    /** @MintWare\JOM\JsonField(name="other_protected", type="string") */
    protected $otherProtectedProp;

    public function getProtectedProp()
    {
        return $this->protectedProp;
    }

    public function setProtectedProp($protectedProp)
    {
        $this->protectedProp = $protectedProp;
    }

    public function getOtherProtectedProp()
    {
        return $this->otherProtectedProp;
    }

    public function addOtherProtectedProp($otherProtectedProp)
    {
        $this->otherProtectedProp = $otherProtectedProp;
    }
}
