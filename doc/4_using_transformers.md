# Using Transformers
[📝 Go to index](./index.md)

Often it's required to transform the data before mapping, during the mapping or after the mapping.  
E.q. a special time format needs to be converted to an DateTime object.


```json
{
    "german_date_time": "27.04.2018, 22:05:47 Uhr"
}
```

For this case, you can create a class which implements the `TransformerInterface`:

```php
class GermanDateTimeTransformer implements MintWare\JOM\TransformerInterface
{
    // Converts "27.04.2018, 22:05:47 Uhr" to a DateTime object
    public static function transform($data)
    {
        list($date, $time) = explode(', ', substr($data, 0, -4));
        return new DateTime($date . ' ' . $time);
    }

    // Converts a DateTime object to "27.04.2018, 22:05:47 Uhr"
    public static function reverseTransform($data)
    {
        $formatted = null;
        if ($data != null) {
            $formatted = $data->format('d.m.Y, H:i:s') . ' Uhr';
        }

        return $formatted;
    }
}
```

In the object you can refer the transformer by its FQCN:

```php
class SomeObject
{
    /** @JsonField(name="german_date_time", type="string", postTransformer="\GermanDateTimeTransformer") */
    public $dateTime;
}
```

Available arguments are `preTransformer`, `transformer` and `postTransformer` 