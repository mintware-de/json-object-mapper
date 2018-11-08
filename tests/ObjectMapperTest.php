<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\JOM;

use MintWare\JOM\Exception\ClassNotFoundException;
use MintWare\JOM\Exception\InvalidJsonException;
use MintWare\JOM\Exception\PropertyNotAccessibleException;
use MintWare\JOM\Exception\TypeMismatchException;
use MintWare\JOM\ObjectMapper;
use MintWare\Tests\JOM\Objects\Address;
use MintWare\Tests\JOM\Objects\Autobot;
use MintWare\Tests\JOM\Objects\Person;
use MintWare\Tests\JOM\Objects\PersonWithEscapedFQCN;
use MintWare\Tests\JOM\Objects\PersonWithMultipleAddresses;
use MintWare\Tests\JOM\Objects\SimplePerson;
use PHPUnit\Framework\TestCase;

class ObjectMapperTest extends TestCase
{
    public function testConstruct()
    {
        $mapper = new ObjectMapper();
        $this->assertTrue($mapper instanceof \MintWare\JOM\ObjectMapper);
    }

    public function testMapJsonFailsInvalidJson()
    {
        $mapper = new ObjectMapper();
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('The JSON is not valid.');
        $mapper->mapJson('{"foo', null);
    }

    public function testMapDataToObjectFailsClassNotFound()
    {
        $mapper = new ObjectMapper();
        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('The class Foo\Bar was not found.');
        $mapper->mapDataToObject(json_decode('{"foo": 1}'), 'Foo\\Bar');
    }

    public function testMapDataToObjectFailsPropertyNotAccessible()
    {
        $mapper = new ObjectMapper();
        $this->expectException(PropertyNotAccessibleException::class);
        $this->expectExceptionMessage('Neither the property "name" nor one of the methods "setName", "addName" (or getter) have public access.');
        $mapper->mapDataToObject(json_decode('{"foo": 1}', true), FailPerson::class);
    }

    public function testMapDataToObjectFailsTypeMismatchInteger()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected int got boolean. Property name: age');
        $mapper->mapDataToObject(['age' => false], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchFloat()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected float got integer. Property name: height');
        $mapper->mapDataToObject(['height' => 1], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchBoolean()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected bool got string. Property name: is_cool');
        $mapper->mapDataToObject(['is_cool' => 'red'], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchArray()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected array got object. Property name: nicknames');
        $mapper->mapDataToObject(['nicknames' => (object)[]], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchString()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected string got array. Property name: firstname');
        $mapper->mapDataToObject(['firstname' => []], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchObject()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected object got boolean. Property name: dictionary');
        $mapper->mapDataToObject(['dictionary' => false], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchDatetime()
    {
        $mapper = new ObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected datetime got string. Property name: created');
        $mapper->mapDataToObject(['created' => "Hello World"], Person::class);
    }

    public function testMapDataToObject()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person.json'), true);

        /** @var Person $person */
        $person = $mapper->mapDataToObject($data, Person::class);
        $this->assertSame('Pete', $person->name);
        $this->assertSame('Peterson', $person->surname);
        $this->assertSame(28, $person->age);
        $this->assertSame(1.72, $person->height);
        $this->assertTrue($person->isCool);
        $this->assertSame(['Pepe', 'Pete'], $person->nicknames);
        $this->assertEquals((object)['hello' => 'Hi', 'bye' => 'Ciao!'], $person->dictionary);
        $this->assertEquals(strtotime('2017-03-08T09:41:00'), $person->created->getTimestamp());
        $this->assertEquals(strtotime('9.9.2017 00:00:00'), $person->updated->getTimestamp());
        $this->assertEquals(strtotime('10.9.2017 00:00:00'), $person->deleted->getTimestamp());
        $address = new Address();
        $address->street = 'Mainstreet 22a';
        $address->zipCode = 'A-12345';
        $address->town = 'Best Town';
        $address->country = 'Germany';
        $this->assertEquals($address, $person->address);
    }

    public function testMapDataToObjectMultipleTypes()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person_string_address.json'), true);

        /** @var Person $person */
        $person = $mapper->mapDataToObject($data, Person::class);
        $this->assertSame('Pete', $person->name);
        $this->assertSame('Peterson', $person->surname);
        $this->assertSame(28, $person->age);
        $this->assertSame(1.72, $person->height);
        $this->assertTrue($person->isCool);
        $this->assertSame(['Pepe', 'Pete'], $person->nicknames);
        $this->assertEquals((object)['hello' => 'Hi', 'bye' => 'Ciao!'], $person->dictionary);
        $this->assertEquals(strtotime('2017-03-08T09:41:00'), $person->created->getTimestamp());
        $this->assertEquals(strtotime('9.9.2017 00:00:00'), $person->updated->getTimestamp());
        $this->assertEquals(strtotime('10.9.2017 00:00:00'), $person->deleted->getTimestamp());
        $this->assertSame("Mainstreet 22a, A-12345, Best Town, Germany", $person->address);
    }

    public function testMapDataToObjectMultiple()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person_multiple_addresses.json'), true);

        /** @var PersonWithMultipleAddresses $person */
        $person = $mapper->mapDataToObject($data, PersonWithMultipleAddresses::class);
        $address1 = new Address();
        $address1->street = 'Mainstreet 22a';
        $address1->zipCode = 'A-12345';
        $address1->town = 'Best Town';
        $address1->country = 'Germany';

        $address2 = new Address();
        $address2->street = 'Otherstreet #1';
        $address2->zipCode = 'A-54321';
        $address2->town = 'Great Town';
        $address2->country = 'Austria';
        $this->assertEquals([$address1, $address2], $person->addresses);
    }

    public function testMapDataToObjectWithEscapedFQCN()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person_multiple_addresses.json'), true);

        /** @var PersonWithMultipleAddresses $person */
        $person = $mapper->mapDataToObject($data, PersonWithEscapedFQCN::class);
        $address1 = new Address();
        $address1->street = 'Mainstreet 22a';
        $address1->zipCode = 'A-12345';
        $address1->town = 'Best Town';
        $address1->country = 'Germany';

        $address2 = new Address();
        $address2->street = 'Otherstreet #1';
        $address2->zipCode = 'A-54321';
        $address2->town = 'Great Town';
        $address2->country = 'Austria';
        $this->assertEquals([$address1, $address2], $person->addresses);
    }

    public function testMapDataToObjectWithProtected()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person_protected.json'), true);

        /** @var Person $person */
        $person = $mapper->mapDataToObject($data, Person::class);
        $this->assertEquals('1234', $person->getProtectedProp());
        $this->assertEquals('asdf', $person->getOtherProtectedProp());
    }

    public function testMapJson()
    {
        $mapper = new ObjectMapper();
        $json = file_get_contents(__DIR__ . '/res/person.json');

        /** @var Person $person */
        $person = $mapper->mapJson($json, Person::class);
        $this->assertSame('Pete', $person->name);
        $this->assertSame('Peterson', $person->surname);
        $this->assertSame(28, $person->age);
        $this->assertSame(1.72, $person->height);
        $this->assertTrue($person->isCool);
        $this->assertSame(['Pepe', 'Pete'], $person->nicknames);
        $this->assertEquals((object)['hello' => 'Hi', 'bye' => 'Ciao!'], $person->dictionary);
        $address = new Address();
        $address->street = 'Mainstreet 22a';
        $address->zipCode = 'A-12345';
        $address->town = 'Best Town';
        $address->country = 'Germany';
        $this->assertEquals($address, $person->address);
    }

    public function testMapJsonArray()
    {
        $mapper = new ObjectMapper();
        $json = file_get_contents(__DIR__ . '/res/person_multiple.json');

        /** @var Person $person */
        $persons = $mapper->mapJson($json, Person::class . '[]');
        $this->assertTrue(is_array($persons));
        $this->assertCount(2, $persons);

        $person1 = $persons[0];
        $this->assertSame('Pete', $person1->name);
        $this->assertSame('Peterson', $person1->surname);
        $this->assertSame(28, $person1->age);
        $this->assertSame(1.72, $person1->height);
        $this->assertFalse($person1->isCool);

        $person2 = $persons[1];
        $this->assertSame('Anna', $person2->name);
        $this->assertSame('Anderson', $person2->surname);
        $this->assertSame(25, $person2->age);
        $this->assertSame(1.63, $person2->height);
        $this->assertTrue($person2->isCool);
    }

    public function testObjectToJsonFailsPropertyNotAccessible()
    {
        $mapper = new ObjectMapper();
        $this->expectException(PropertyNotAccessibleException::class);
        $this->expectExceptionMessage('Neither the property "name" nor one of the methods "setName", "addName" (or getter) have public access.');
        $mapper->objectToJson(new FailPerson());
    }


    public function testObjectToJsonBasic()
    {
        $mapper = new ObjectMapper();
        $json = file_get_contents(__DIR__ . '/res/person.json');

        $personOld = $mapper->mapJson($json, SimplePerson::class);
        $reversedJson = $mapper->objectToJson($personOld);
        $personNew = $mapper->mapJson($reversedJson, SimplePerson::class);

        $this->assertEquals($personOld, $personNew);
    }

    public function testObjectToJson()
    {
        $mapper = new ObjectMapper();
        $json = file_get_contents(__DIR__ . '/res/person.json');

        $personOld = $mapper->mapJson($json, Person::class);
        $reversedJson = $mapper->objectToJson($personOld);
        $personNew = $mapper->mapJson($reversedJson, Person::class);

        $this->assertEquals($personOld, $personNew);
    }

    public function testObjectToJsonAdvanced()
    {
        $mapper = new ObjectMapper();
        $json = file_get_contents(__DIR__ . '/res/person_multiple_addresses.json');

        /** @var Person $person */
        $person = $mapper->mapJson($json, PersonWithMultipleAddresses::class);

        $reversedJson = $mapper->objectToJson($person);
        // Normalize line endings
        $json = str_replace(["\r\n", "\r", "\n"], "\n", $json);
        $reversedJson = str_replace(["\r\n", "\r", "\n"], "\n", $reversedJson);

        $this->assertEquals($json, $reversedJson);
    }

    public function testObjectToJsonFailsTypeMismatchInteger()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->age = false;
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected int got boolean. Property name: age');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchString()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->name = new \DateTime();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected string got object. Property name: name');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchFloat()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->height = "hello :-)";
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected float got string. Property name: height');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchBool()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->isCool = 1.322;
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected bool got double. Property name: isCool');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchArray()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->nicknames = 1;
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected array got integer. Property name: nicknames');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchObject()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->dictionary = "asd";
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected object got string. Property name: dictionary');
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonNotFailsTypeMismatchArrayObject()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->dictionary = ['nice' => "asd"];
        $mapper->objectToJson($p);
    }

    public function testObjectToJsonFailsTypeMismatchDateTime()
    {
        $mapper = new ObjectMapper();
        $p = new Person();
        $p->created = true;
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected datetime got boolean. Property name: created');
        $mapper->objectToJson($p);
    }

    public function testTransformers()
    {
        $mapper = new ObjectMapper();
        $data = file_get_contents(__DIR__ . '/res/transformer.json');

        /** @var Autobot $autobot */
        $autobot = $mapper->mapJson($data, Autobot::class);
        $this->assertInstanceOf(\DateTime::class, $autobot->createdAt);
        $this->assertEquals(['yellow', 'black', 'bumblebee', 'json', 'transformer'], $autobot->tags);
        $this->assertEquals(7, $autobot->series);

        $reversedJson = <<<JSON
{
    "created_at": "1515533307000",
    "tags": "yellow,black,bumblebee,json,transformer",
    "series": "000007"
}
JSON;
        $this->assertEquals($reversedJson, $mapper->objectToJson($autobot));
    }

    public function testMapDataToObjectWithAlternativeName()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person_field_variation.json'), true);

        /** @var Person $person */
        $person = $mapper->mapDataToObject($data, Person::class);
        $this->assertSame('Pete', $person->name);
        $this->assertSame('Peterson', $person->surname);
        $this->assertSame(28, $person->age);
        $this->assertSame(1.72, $person->height);
        $this->assertTrue($person->isCool);
        $this->assertSame(['Pepe', 'Pete'], $person->nicknames);
        $this->assertEquals((object)['hello' => 'Hi', 'bye' => 'Ciao!'], $person->dictionary);
        $this->assertEquals(strtotime('2017-03-08T09:41:00'), $person->created->getTimestamp());
        $this->assertEquals(strtotime('9.9.2017 00:00:00'), $person->updated->getTimestamp());
        $this->assertEquals(strtotime('10.9.2017 00:00:00'), $person->deleted->getTimestamp());
        $address = new Address();
        $address->street = 'Mainstreet 22a';
        $address->zipCode = 'A-12345';
        $address->town = 'Best Town';
        $address->country = 'Germany';
        $this->assertEquals($address, $person->address);
    }

    public function testMapDataToObjectWithoutNaming()
    {
        $mapper = new ObjectMapper();
        $data = json_decode(file_get_contents(__DIR__ . '/res/person.json'), true);

        /** @var SimplePerson $person */
        $person = $mapper->mapDataToObject($data, SimplePerson::class);
        $this->assertSame('Pete', $person->firstname);
        $this->assertSame('Peterson', $person->surname);
        $this->assertSame(28, $person->age);
        $this->assertSame(1.72, $person->height);
        $this->assertTrue($person->is_cool);
        $this->assertSame(['Pepe', 'Pete'], $person->nicknames);
        $this->assertEquals(['hello' => 'Hi', 'bye' => 'Ciao!'], $person->dictionary);
        $address = [
            'street' => 'Mainstreet 22a',
            'town' => 'Best Town',
            'country' => 'Germany',
            'zip_code' => 'A-12345',
        ];
        $this->assertEquals($address, $person->address);
    }
}
