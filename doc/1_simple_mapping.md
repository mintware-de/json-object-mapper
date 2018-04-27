# Simple Data Mapping
[📝 Go to index](./index.md)

If the json structure equals to your objects, the mapping is pretty easy.  
You only need to add the `@JsonField`-Annotation to the properties you wish to map, instantiate the mapper and call the `mapDataToObject()`-Method


For example you have a json file like this
```json
{
    "firstname": "Pete",
    "surname": "Peterson",
    "age": 28,
    "height": 1.72,
    "is_cool": true,
    "nicknames": [
        "Pepe",
        "Pete"
    ]
}
```

And want to map the json to this object:
```php
use MintWare\JOM\JsonField;

class SimplePerson
{
    /** @JsonField */
    public $firstname;

    /** @JsonField */
    public $surname;

    /** @JsonField */
    public $age;

    /** @JsonField */
    public $is_cool;

    /** @JsonField */
    public $nicknames;
}
```

To map the JSON to the object you need to call the `mapDataToObject()`-method
```php
$mapper = new MintWare\JOM\ObjectMapper();
$data = json_decode(file_get_contents('person.json'), true);
$person = $mapper->mapDataToObject($data, SimplePerson::class);

var_dump($person);
```