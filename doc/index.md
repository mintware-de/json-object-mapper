# JSON Object Mapper Documentation

## Examples
1. [Simple Mapping](./1_simple_mapping.md)
2. [Mapping with Typehints](./2_mapping_with_typehints.md)
3. [Mapping Alternative Property Names](./3_mapping_alternative_property_names.md)
4. [Using Transformers](./4_using_transformers.md)
5. [Convert an Mapped Object to JSON](./5_convert_an_object_to_json.md)
6. [Multiple Annotations](./6_multiple_annotations.md)


## Basic usage
JOM is totally easy to use. 
Simply put the annotation `@MintWare\JOM\JsonField()` to your class properties.

## Example
Dataset:
```json
{
    "first_name": "Pete",
    "surname": "Peterson",
    "age": 28,
    "address": {
        "street": "Mainstreet 22a",
        "zip_code": "A-12345",
        "town": "Best Town"
    }
}
```

Data Class:
```php
<?php

use MintWare\JOM\JsonField;

class Person
{
    /** @JsonField(name="first_name", type="string") */
    public $firstName;
    
    /** @JsonField(name="surname", type="string") */
    public $lastname;
    
    /** @JsonField() */
    public $age;
    
    /** @JsonField(type="Some\Other\DataClass\Address") */
    public $address;
}
```

Map the JSON:
```php
<?php

use  MintWare\JOM\ObjectMapper;

$mapper = new ObjectMapper();
$person = $mapper->mapJson(file_get_contents('person.json'), Person::class);
```