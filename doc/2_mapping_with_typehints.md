# Mapping Data with Typehints
[📝 Go to index](./index.md)

The object mapper can also map nested data.
For example you have in your JSON a property which contains a address for the user:

```json
{
    "firstname": "Pete",
    "surname": "Peterson",
    "address": {
        "street": "Mainstreet 22a",
        "zip_code": "A-12345",
        "town": "Best Town",
        "country": "Germany"
    }
}
```

Your user and address object looks like this

```php
<?php

class User {
    /** @JsonField() */
    public $firstname;

    /** @JsonField() */
    public $surname;

    /** @JsonField() */
    public $address;
}

class Address {

    /** @JsonField() */
    public $street;
    
    /** @JsonField() */
    public $zip_code;
    
    /** @JsonField() */
    public $town;
    
    /** @JsonField() */
    public $country;
}
```

If you map the json to this user object, the address property would become an array - but we want an instance of Address.

In this case you need to modify the annotation of the `$address` property and add the argument `type` :

```php
class User {
    // ...

    /** @JsonField(type="Address") */
    public $address;
}
```

With this, the `$address` property will be mapped as an instance of `Address` 