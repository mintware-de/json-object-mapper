# Legacy Upgrade Guide

If you're using `mintware-de/json-object-mapper` you can use this guide for upgrading.

## Update composer.json
Open your `composer.json` and replace
```text
"mintware-de/json-object-mapper": "^1.1"
```

with
```text
"mintware-de/data-model-mapper": "^1.0",
"mintware-de/dmm-json": "^1.0"
```

then run
```bash
$ composer update
```

## Update your source code

Find and replace:

```text
FIND:     MintWare\JOM\
REPLACE:  MintWare\DMM\
```

```text
FIND:     \JsonField
REPLACE:  \DataField
```
```text
FIND:     @JsonField
REPLACE:  @DataField
```

Fix this calls:
```php
<?php

// find
$mapper = new ObjectMapper();

// replace with
use MintWare\DMM\Serializer\JsonSerializer;
$mapper = new ObjectMapper(new JsonSerializer());
```

```php
<?php

// find
$mapper->mapJson(...);

// replace with
use MintWare\DMM\Serializer\JsonSerializer;
$mapper->map(...);
```
```php
<?php

// find
$var = $mapper->objectToJson(...);

// replace with
$var = $mapper->serialize(...);
```