# Mapping Alternative Property Names
[📝 Go to index](./index.md)

Sometimes it is useful or required to map the property names from the json to another property name in the class object.
(e.g. the property uses incompatible characters or the naming is not good):

```json
{
    "first name": "Pete",
    "lastname": "Peterson",
    "foo*bar": "baz"
}
```

In this case you can use the `name` argument in the annotation to refer the name of the property in the json object

```php
<?php

class User {
    /** @JsonField(name="first name") */
    public $firstname;

    /** @JsonField(name="lastname") */
    public $surname;

    /** @JsonField(name="foo*bar") */
    public $aFunnyMessage;
}
```

The mapped object would be something like that:

```
User Object
(
    [firstname] => Pete
    [surname] => Peterson
    [aFunnyMessage] => baz
)
```