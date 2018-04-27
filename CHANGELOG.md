# v1.1.4 
- Added support for multiple `@JsonField`-Annotations to support multiple, different property names
- Simple mapping method added. You only need to add `@JsonField()` if the property in json and the class property equals

# v1.1.3 
**Transformers implemented**

`preTransformer`, `transformer` and `postTransformer` in `@JsonField`-Annotation implemented  
For details, take a look on [tests/Objects/Autobot.php](tests/Objects/Autobot.php)

# v1.1.2
- `TypeMismatchException` contains now the name of the property
- Redundant method calls simplified

# v1.1.1
**Object to JSON conversion added**

Use the`objectToJson()`-Method to convert an object back to JSON

# v1.1.0
**Multiple primitive types are now supported**

Possible values: `primitive|FQCN`, `primitive|primitive|primitive`, `primitive|primitive|FQCN`

Example: `string|integer|My\Class`, `string|int|datetime`

**NOT** SUPPORTED: `My\Class|Another\Class`, `My\Class|string`

# v1.0.3
- Additional Date Format added

# v1.0.2
**Date and DateTime Type added**

Supports the following formats
- 2017-09-09
- 2017-09-09 13:20:59
- 2017-09-09T13:20:59
- 2017-09-09T13:20:59.511Z
- 2017-09-09T13:20:59-02:00

and of course Unix-Timestamps

# v1.0.1
- ParseError replaced with InvalidJsonException to keep PHP5 compatibility
- The JsonField-Annotation property "type" supports now escaped FQCNs
- Travis-CI support added

# v1.0.0
- Initial Release