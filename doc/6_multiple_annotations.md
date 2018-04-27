# Multiple Annotations
[📝 Go to index](./index.md)

In some edge-cases a property name in the json can vary:
```json
[
    {"first name": "Foo"},
    {"first*name": "Bar"},
    {"first-name": "Baz"},
    {"first_name": "FooBarBaz"}
]
```

In this case you can add multiple `@JsonField` annotations for a property.

```php
class CrappySourceData {
    /**
     * @JsonField(name="first name") 
     * @JsonField(name="first*name") 
     * @JsonField(name="first-name") 
     * @JsonField(name="first_name") 
     */
    public $firstName;
}
```