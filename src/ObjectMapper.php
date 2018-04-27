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
use MintWare\JOM\Exception\InvalidJsonException;
use MintWare\JOM\Exception\PropertyNotAccessibleException;
use MintWare\JOM\Exception\TypeMismatchException;

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

    /**
     * Instantiates a new json object mapper
     */
    public function __construct()
    {
        // Symfony does this also.. ;-)
        AnnotationRegistry::registerLoader('class_exists');

        // Set the annotation reader
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $this->reader = new AnnotationReader($parser);
    }

    /**
     * Maps a JSON string to a object
     *
     * @param string $json The JSON string
     * @param string $targetClass The target object class
     *
     * @return mixed The mapped object
     *
     * @throws InvalidJsonException If the JSON is not valid
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the class property has no public access an no set-Method
     * @throws TypeMismatchException If The type in the JSON does not match the type in the class
     */
    public function mapJson($json, $targetClass)
    {
        // Check if the JSON is valid
        if (!is_array($data = json_decode($json, true))) {
            throw new InvalidJsonException();
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
                if (isset($data[$field->name])) {
                    $val = null;

                    $types = explode('|', $field->type);
                    $typeKeys = array_keys($types);
                    $lastTypeKey = end($typeKeys);

                    if ($field->preTransformer !== null) {
                        $preTransformer = $field->preTransformer;
                        $data[$field->name] = $preTransformer::transform($data[$field->name]);
                    }

                    if ($field->transformer !== null) {
                        $transformer = $field->transformer;
                        $val = $transformer::transform($data[$field->name]);
                        $types = []; // Ignore type handler!
                    }

                    foreach ($types as $typeKey => $type) {
                        $isLastElement = ($typeKey == $lastTypeKey);
                        // Check the type of the field and set the val
                        switch (strtolower($type)) {
                            case 'int':
                            case 'integer':
                                if (!is_int($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (int)$data[$field->name];
                                break;
                            case 'float':
                            case 'double':
                            case 'real':
                                if (!is_float($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (double)$data[$field->name];
                                break;
                            case 'bool':
                            case 'boolean':
                                if (!is_bool($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (bool)$data[$field->name];
                                break;
                            case 'array':
                                if (!is_array($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (array)$data[$field->name];
                                break;
                            case 'string':
                                if (!is_string($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (string)$data[$field->name];
                                break;
                            case 'object':
                                $tmpVal = $data[$field->name];
                                if (is_array($tmpVal) && array_keys($tmpVal) != range(0, count($tmpVal))) {
                                    $data[$field->name] = (object)$tmpVal;
                                }
                                if (!is_object($data[$field->name])) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }
                                $val = (object)$data[$field->name];
                                break;
                            case 'date':
                            case 'datetime':
                                // Accepts the following formats:
                                // 2017-09-09
                                // 2017-09-09 13:20:59
                                // 2017-09-09T13:20:59
                                // 2017-09-09T13:20:59.511
                                // 2017-09-09T13:20:59.511Z
                                // 2017-09-09T13:20:59-02:00
                                $validPattern = '~^\d{4}-\d{2}-\d{2}((T|\s{1})\d{2}:\d{2}:\d{2}(\.\d{1,3}(Z|)|(\+|\-)\d{2}:\d{2}|)|)$~';

                                $tmpVal = $data[$field->name];
                                if (preg_match($validPattern, $tmpVal)) {
                                    $data[$field->name] = new \DateTime($tmpVal);
                                } else {
                                    $casted = intval($tmpVal);
                                    if (is_numeric($tmpVal) || ($casted == $tmpVal && strlen($casted) == strlen($tmpVal))) {
                                        $data[$field->name] = new \DateTime();
                                        $data[$field->name]->setTimestamp($tmpVal);
                                    }
                                }

                                if ($data[$field->name] instanceof \DateTime === false) {
                                    if ($isLastElement) {
                                        throw new TypeMismatchException($type, gettype($data[$field->name]), $field->name);
                                    }
                                    break;
                                }

                                if (strtolower($type) == 'date') {
                                    $data[$field->name]->setTime(0, 0, 0);
                                }

                                $val = $data[$field->name];
                                break;
                            case '':
                                $val = $data[$field->name];
                                break;
                            default:
                                // If none of the primitives above match it is an custom object

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
                                break;
                        }

                        if ($field->postTransformer !== null) {
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
                $postTransformer = $field->postTransformer;
                $val = $postTransformer::reverseTransform($val);
            }

            if ($field->transformer !== null) {
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
                    if (!is_object($val)) {
                        break;
                    }
                    if (!in_array(strtolower($tString), [
                        'int', 'integer',
                        'float', 'double', 'real',
                        'bool', 'boolean',
                        'array',
                        'string',
                        'object',
                        'date', 'datetime'])) {
                        break;
                    }
                }
                // Check the type of the field and set the val
                switch (strtolower($type)) {
                    case 'int':
                    case 'integer':
                        if (!is_int($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (int)$val;
                        break;
                    case 'float':
                    case 'double':
                    case 'real':
                        if (!is_float($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (double)$val;
                        break;
                    case 'bool':
                    case 'boolean':
                        if (!is_bool($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (bool)$val;
                        break;
                    case 'array':
                        if (!is_array($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (array)$val;
                        break;
                    case 'string':
                        if (!is_string($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (string)$val;
                        break;
                    case 'object':
                        $tmpVal = $val;
                        if (is_array($tmpVal) && array_keys($tmpVal) != range(0, count($tmpVal))) {
                            $val = (object)$tmpVal;
                        }
                        if (!is_object($val)) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }
                        $val = (object)$val;
                        break;
                    case 'date':
                    case 'datetime':
                        if ($val instanceof \DateTime === false) {
                            throw new TypeMismatchException($type, gettype($val), $propertyName);
                        }

                        $format = 'Y-m-d\TH:i:s';
                        if ($field instanceof DateTimeField && $field->format !== null) {
                            $format = $field->format;
                        }

                        /** @var \DateTime $val */
                        if (strtolower($format) !== 'timestamp') {
                            $val = $val->format($format);
                        } else {
                            $val = $val->getTimestamp();
                        }
                        break;
                    default:
                        // If none of the primitives above match it is an custom object

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
                        break;
                }
            }

            if ($field->preTransformer !== null) {
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
}
