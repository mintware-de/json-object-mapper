<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use MintWare\JOM\Exception\ClassNotFoundException;
use MintWare\JOM\Exception\PropertyNotAccessibleException;
use MintWare\JOM\Exception\SerializerException;
use MintWare\JOM\Exception\TypeMismatchException;
use MintWare\JOM\Serializer\JsonSerializer;
use MintWare\JOM\Serializer\SerializerInterface;

/**
 * This class is the object mapper
 * To map a json string to a object you can easily call the
 * ObjectMapper::mapJson($json, $targetClass) method.
 *
 * @package MintWare\JOM
 */
class ObjectMapper
{
    /** @var AnnotationReader */
    protected $reader = null;

    /** @var SerializerInterface */
    private $serializer = null;

    private $primitives = [
        'int', 'integer',
        'float', 'double', 'real',
        'bool', 'boolean',
        'array',
        'string',
        'object',
        'date', 'datetime'
    ];

    /**
     * Instantiates a new json object mapper
     * @throws \Exception If the Annotation reader could not be initialized
     */
    public function __construct()
    {
        $this->serializer = new JsonSerializer();

        // Symfony does this also.. ;-)
        AnnotationRegistry::registerLoader('class_exists');

        // Set the annotation reader
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        try {
            $this->reader = new AnnotationReader($parser);
        } catch (\Exception $e) {
            throw new \Exception("Failed to initialize the AnnotationReader", null, $e);
        }
    }

    /**
     * Maps raw data to a object
     *
     * @param string $rawData The raw data
     * @param string $targetClass The target object class
     *
     * @return mixed The mapped object
     *
     * @throws SerializerException If the data couldn't be deserialized
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the class property has no public access an no set-Method
     * @throws TypeMismatchException If The type in the JSON does not match the type in the class
     * @throws \ReflectionException If the target class does not exist
     */
    public function mapJson($rawData, $targetClass)
    {
        // Deserialize the data
        try {
            $data = $this->serializer->deserialize($rawData);
        } catch (\Exception $e) {
            throw new SerializerException('Deserialize failed: ' . $e->getMessage(), 0, $e);
        }

        // Pre initialize the result
        $result = null;

        // Check if the target object is a collection of type X
        if (substr($targetClass, -2) == '[]') {
            $result = [];
            foreach ($data as $key => $entryData) {
                // Map the data recursive
                $result[] = $this->mapDataToObject($entryData, substr($targetClass, 0, -2));
            }
        } else {
            // Map the data recursive
            $result = $this->mapDataToObject($data, $targetClass);
        }

        return $result;
    }

    /**
     * Maps the  current entry to the property of the object
     *
     * @param array $data The array of data
     * @param string $targetClass The current object class
     *
     * @return mixed The mapped object
     *
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the mapped property is not accessible
     * @throws TypeMismatchException If the given type in json does not match with the expected type
     * @throws \ReflectionException If the class does not exist.
     */
    public function mapDataToObject($data, $targetClass)
    {
        $targetClass = preg_replace('~(\\\\){2,}~', '\\', $targetClass);

        // Check if the target object class exists, if not throw an exception
        if (!class_exists($targetClass)) {
            throw new ClassNotFoundException($targetClass);
        }

        // Create the target object
        $object = new $targetClass();

        // Reflecting the target object to extract properties etc.
        $class = new \ReflectionClass($targetClass);

        // Iterate over each class property to check if it's mapped
        foreach ($class->getProperties() as $property) {

            // Extract the Annotations
            $fields = $this->reader->getPropertyAnnotations($property);

            /** @var JsonField $field */
            foreach ($fields as $field) {
                if ($field instanceof JsonField == false) {
                    continue;
                }

                // Check if the property is public accessible or has a setter / adder
                $propertyName = $property->getName();
                $ucw = ucwords($propertyName);
                if (!$property->isPublic() && !($class->hasMethod('set' . $ucw) || $class->hasMethod('add' . $ucw))) {
                    throw new PropertyNotAccessibleException($propertyName);
                }

                if ($field->name == null) {
                    $field->name = $propertyName;
                }

                // Check if the current property is defined in the JSON
                if (!isset($data[$field->name])) continue;
                $val = null;

                $types = explode('|', $field->type);
                $typeKeys = array_keys($types);
                $lastTypeKey = end($typeKeys);

                if ($field->preTransformer !== null) {
                    /** @var TransformerInterface $preTransformer */
                    $preTransformer = $field->preTransformer;
                    $data[$field->name] = $preTransformer::transform($data[$field->name]);
                }

                if ($field->transformer !== null) {
                    /** @var TransformerInterface $transformer */
                    $transformer = $field->transformer;
                    $val = $transformer::transform($data[$field->name]);
                    $types = []; // Ignore type handler!
                }

                foreach ($types as $typeKey => $type) {
                    $isLastElement = ($typeKey == $lastTypeKey);

                    // Check the type of the field and set the val
                    if ($type == '') {
                        $val = $data[$field->name];
                    } elseif (in_array($type, $this->primitives)) {
                        $format = ($field instanceof DateTimeField && $field->format !== null
                            ? $field->format
                            : 'Y-m-d\TH:i:s');

                        $converted = null;
                        try {
                            $converted = $this->castType($data[$field->name], $type, $field->name, $format, true);
                        } catch (TypeMismatchException $ex) {
                            if ($isLastElement) {
                                throw  $ex;
                            }
                            continue;
                        }
                        $val = $converted;
                    } else {
                        // If none of the primitives match it is an custom object

                        // Check if it's an array of X
                        if (substr($type, -2) == '[]' && is_array($data[$field->name])) {
                            $t = substr($type, 0, -2);
                            $val = [];
                            foreach ($data[$field->name] as $entry) {
                                // Map the data recursive
                                $val[] = (object)$this->mapDataToObject($entry, $t);
                            }
                        } elseif (substr($type, -2) != '[]') {
                            // Map the data recursive
                            $val = (object)$this->mapDataToObject($data[$field->name], $type);
                        }
                    }

                    if ($field->postTransformer !== null) {
                        /** @var TransformerInterface $postTransformer */
                        $postTransformer = $field->postTransformer;
                        $val = $postTransformer::transform($val);
                    }

                    if ($val !== null) {
                        break;
                    }
                }

                // Assign the JSON data to the object property
                if ($val !== null) {
                    // If the property is public accessible, set the value directly
                    if ($property->isPublic()) {
                        $object->{$propertyName} = $val;
                    } else {
                        // If not, use the setter / adder
                        $ucw = ucwords($propertyName);
                        if ($class->hasMethod($method = 'set' . $ucw)) {
                            $object->$method($val);
                        } elseif ($class->hasMethod($method = 'add' . $ucw)) {
                            $object->$method($val);
                        }
                    }
                }
            }
        }
        return $object;
    }

    /**
     * Serializes an object to JSON
     *
     * @param object $object The object
     * @param bool $returnAsString For internal usage
     * @return string|array The JSON-String
     *
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the mapped property is not accessible
     * @throws TypeMismatchException If the given type in json does not match with the expected type
     */
    public function objectToJson($object, $returnAsString = true)
    {
        $jsonData = [];
        // Reflecting the target object to extract properties etc.
        $class = new \ReflectionObject($object);

        // Iterate over each class property to check if it's mapped
        foreach ($class->getProperties() as $property) {
            // Extract the JsonField Annotation

            /** @var JsonField $field */
            $field = $this->reader->getPropertyAnnotation($property, JsonField::class);

            // Is it not defined, the property is not mapped
            if (null === $field) {
                continue;
            }

            // Check if the property is public accessible or has a setter / adder
            $propertyName = $property->getName();
            $ucw = ucwords($propertyName);
            if (!$property->isPublic() && !($class->hasMethod('get' . $ucw))) {
                throw new PropertyNotAccessibleException($propertyName);
            }

            $val = null;
            if ($property->isPublic()) {
                $val = $object->{$propertyName};
            } else {
                $val = $object->{'get' . $ucw}();
            }

            // Reverse order on encoding (postTransformer -> transformer -> preTransformer)
            if ($field->postTransformer !== null) {
                /** @var TransformerInterface $postTransformer */
                $postTransformer = $field->postTransformer;
                $val = $postTransformer::reverseTransform($val);
            }

            if ($field->transformer !== null) {
                /** @var TransformerInterface $transformer */
                $transformer = $field->transformer;
                $val = $transformer::reverseTransform($val);
            }

            if (is_null($val)) {
                $jsonData[$field->name] = $val;
                continue;
            }

            if ($field->transformer === null) {
                $types = explode('|', $field->type);
                $type = null;

                foreach ($types as $tString) {
                    $type = $tString;
                    if (!is_object($val) || !in_array(strtolower($tString), $this->primitives)) {
                        break;
                    }
                }
                // Check the type of the field and set the val
                if (in_array($type, $this->primitives)) {
                    $format = 'Y-m-d\TH:i:s';
                    if ($field instanceof DateTimeField && $field->format !== null) {
                        $format = $field->format;
                    }
                    $val = $this->castType($val, $type, $propertyName, $format);
                } else {
                    // Check if it's an array of X
                    if (substr($type, -2) == '[]' && is_array($val)) {
                        $tmpVal = [];
                        foreach ($val as $entry) {
                            // Map the data recursive
                            $tmpVal[] = (object)$this->objectToJson($entry, false);
                        }
                        $val = $tmpVal;
                    } elseif (substr($type, -2) != '[]') {
                        // Map the data recursive
                        $val = (object)$this->objectToJson($val, false);
                    }
                }
            }

            if ($field->preTransformer !== null) {
                /** @var TransformerInterface $preTransformer */
                $preTransformer = $field->preTransformer;
                $val = $preTransformer::reverseTransform($val);
            }

            // Assign the JSON data to the object property
            if ($val !== null) {
                // If the property is public accessible, set the value directly
                $jsonData[$field->name] = $val;
            }
        }

        $res = $jsonData;
        if ($returnAsString) {
            $res = json_encode($res, JSON_PRETTY_PRINT);
        }

        return $res;
    }

    /**
     * @param mixed $dataToMap The data which should be mapped
     * @param string $type The target type
     * @param string $propertyName The name of the property (required for the exception)
     * @param string $datetimeFormat the format for DateTime deserialization
     * @param bool $fromJson True, if the data comes from the json data
     * @return mixed
     * @throws TypeMismatchException If the data does not match to the type
     *
     * @internal
     */
    private function castType($dataToMap, $type, $propertyName, $datetimeFormat, $fromJson = false)
    {
        $dtCheck = function ($x) {
            return ($x instanceof \DateTime);
        };

        $checkMethod = [
            'int' => 'is_int', 'integer' => 'is_int',
            'float' => 'is_float', 'double' => 'is_float', 'real' => 'is_float',
            'bool' => 'is_bool', 'boolean' => 'is_bool',
            'date' => $dtCheck, 'datetime' => $dtCheck,
            'array' => 'is_array',
            'string' => 'is_string',
            'object' => function ($x) {
                return $x == false ? true : $x;
            },
        ];

        if (!isset($checkMethod[$type])) {
            return null;
        }

        if ($fromJson && in_array($type, ['date', 'datetime'])) {
            // Accepts the following formats:
            // 2017-09-09
            // 2017-09-09 13:20:59
            // 2017-09-09T13:20:59
            // 2017-09-09T13:20:59.511
            // 2017-09-09T13:20:59.511Z
            // 2017-09-09T13:20:59-02:00
            $validPattern = '~^\d{4}-\d{2}-\d{2}((T|\s{1})\d{2}:\d{2}:\d{2}(\.\d{1,3}(Z|)|(\+|\-)\d{2}:\d{2}|)|)$~';

            $tmpVal = $dataToMap;
            if (preg_match($validPattern, $tmpVal)) {
                $dataToMap = new \DateTime($tmpVal);
            } else {
                $casted = intval($tmpVal);
                if (is_numeric($tmpVal) || ($casted == $tmpVal && strlen($casted) == strlen($tmpVal))) {
                    $dataToMap = new \DateTime();
                    $dataToMap->setTimestamp($tmpVal);
                }
            }
        }

        if (!$checkMethod[$type]($dataToMap)) {
            throw new TypeMismatchException($type, gettype($dataToMap), $propertyName);
        }

        if (in_array($type, ['int', 'integer'])) {
            $dataToMap = (int)$dataToMap;
        } elseif (in_array($type, ['float', 'double', 'real'])) {
            $dataToMap = (float)$dataToMap;
        } elseif (in_array($type, ['bool', 'boolean'])) {
            $dataToMap = (bool)$dataToMap;
        } elseif (in_array($type, ['array'])) {
            $dataToMap = (array)$dataToMap;
        } elseif (in_array($type, ['string'])) {
            $dataToMap = (string)$dataToMap;
        } elseif (in_array($type, ['object'])) {
            $tmpVal = $dataToMap;
            if (is_array($tmpVal) && array_keys($tmpVal) != range(0, count($tmpVal))) {
                $dataToMap = (object)$tmpVal;
            }
            if (!is_object($dataToMap)) {
                throw new TypeMismatchException($type, gettype($dataToMap), $propertyName);
            }
            $dataToMap = (object)$dataToMap;
        } elseif (in_array($type, ['date', 'datetime'])) {
            if ($fromJson) {
                if (strtolower($type) == 'date') {
                    $dataToMap->setTime(0, 0, 0);
                }
            } else {

                /** @var \DateTime $dataToMap */
                if (strtolower($datetimeFormat) !== 'timestamp') {
                    $dataToMap = $dataToMap->format($datetimeFormat);
                } else {
                    $dataToMap = $dataToMap->getTimestamp();
                }
            }
        }
        return $dataToMap;
    }
}
