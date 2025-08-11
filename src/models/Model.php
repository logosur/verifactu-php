<?php

declare(strict_types=1);

namespace eseperio\verifactu\models;

/**
 * Abstract base model with validation support.
 * All models must implement the rules() method and toXml() method.
 */
abstract class Model
{
    /**
     * Returns validation rules for model properties.
     * Each rule must be an array: [propertyName, validator]
     * Validator can be a string ('required', 'integer', 'string', 'email', etc) or a callable.
     *
     * Example:
     *  return [
     *      ['systemInfo', 'string'],
     *      ['email', 'email'],
     *      ['amount', 'integer'],
     *      ['customField', function($value) { return is_numeric($value); }]
     *      ['issuerName', 'required']
     *  ];
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Core validator executing rules() with optional field exclusions.
     * @param string[] $exclude Fields to skip during validation
     * @return true|array
     */
    protected function performValidation(array $exclude = [])
    {
        $errors = [];

        foreach ($this->rules() as $rule) {
            $properties = $rule[0];
            $validator = $rule[1];

            // Allow $properties to be string or array
            $properties = is_array($properties) ? $properties : [$properties];

            foreach ($properties as $property) {
                // Skip excluded fields
                if (in_array($property, $exclude, true)) {
                    continue;
                }

                // Try to get value using getter method first
                $getter = 'get' . ucfirst((string) $property);

                $value = method_exists($this, $getter) ? $this->$getter() : $this->$property ?? null;

                if ($validator === 'required') {
                    if ($value === null || $value === '' || ($value === [])) {
                        $errors[$property][] = 'This field is required.';
                    }
                    continue;
                }

                if (is_callable($validator)) {
                    $result = call_user_func($validator, $value, $this);

                    if ($result !== true) {
                        $errors[$property][] = is_string($result) ? $result : "Validation failed for $property.";
                    }
                } else {
                    // Skip validation for string/integer/float validators if value is null (unless marked as required)
                    if ($value === null && in_array($validator, ['string', 'integer', 'float', 'email'])) {
                        continue;
                    }

                    switch ($validator) {
                        case 'string':
                            if (!is_string($value)) {
                                $errors[$property][] = 'Must be a string.';
                            }
                            break;
                        case 'integer':
                            if (!is_int($value)) {
                                $errors[$property][] = 'Must be an integer.';
                            }
                            break;
                        case 'float':
                            if (!is_float($value) && !is_int($value)) {
                                $errors[$property][] = 'Must be a float.';
                            }
                            break;
                        case 'email':
                            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$property][] = 'Must be a valid email address.';
                            }
                            break;
                        default:
                            $errors[$property][] = "Unknown validator: $validator";
                    }
                }
            }
        }

        return $errors === [] ? true : $errors;
    }

    /**
     * Validates model properties based on rules().
     * Returns true if all validations pass, otherwise returns an array of error messages.
     *
     * @return true|array
     */
    public function validate()
    {
        return $this->performValidation();
    }

    /**
     * Validates model properties based on rules(), excluding provided fields.
     * This is useful for partial validations (e.g., before hash generation).
     *
     * @param string[] $exclude
     * @return true|array
     */
    public function validateExcept(array $exclude)
    {
        return $this->performValidation($exclude);
    }

    /**
     * Deprecated: Use validateExcept(['hash']) instead.
     * Kept for backward compatibility.
     *          
     * @return true|array
     */
    public function validateExceptHash()
    {
        return $this->performValidation(['hash']);
    }
}
