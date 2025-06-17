<?php

namespace CodeZone\Bible\Services;

/**
 * Class RequestValidator
 *
 * Validates request data against a set of rules.
 */
class Validator
{
    /**
     * Validate request data against a set of rules.
     *
     * @param RequestInterface|array $dataOrRequest The request to validate
     * @param array $rules The validation rules
     * @return array|bool True if validation passes, array of errors if it fails
     */
    public function validate($dataOrRequest, array $rules)
    {
        $errors = [];
        $data = $dataOrRequest;

        if (!is_array($data) && $data instanceof RequestInterface) {
            return $this->validateRequest($data, $rules);
        }

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = is_array($data) ? ($data[$field] ?? null) : null;

            foreach ($fieldRules as $rule) {
                $result = $this->validateRule($field, $value, $rule);
                if ($result !== true) {
                    $errors[$field] = $result;
                    break; // Stop validating this field once an error is found
                }
            }
        }

        return empty($errors) ? true : $errors;
    }


    public function validateRequest(RequestInterface $request, array $rules)
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $request->get($field);

            foreach ($fieldRules as $rule) {
                $result = $this->validateRule($field, $value, $rule);
                if ($result !== true) {
                    $errors[$field] = $result;
                    break; // Stop validating this field once an error is found
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate a single rule for a field.
     *
     * @param string $field The field name
     * @param mixed $value The field value
     * @param string $rule The rule to validate against
     * @return string|bool True if validation passes, error message if it fails
     */
    protected function validateRule(string $field, $value, string $rule)
    {
        // Handle rules with parameters (e.g., min:3)
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    return __('This field is required.', 'bible-plugin');
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    return __('This field must be a string.', 'bible-plugin');
                }
                break;

            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    return __('This field must be a number.', 'bible-plugin');
                }
                break;

            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return __('This field must be a valid email address.', 'bible-plugin');
                }
                break;

            case 'url':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return __('This field must be a valid URL.', 'bible-plugin');
                }
                break;

            case 'min':
                if ($value !== null) {
                    if (is_string($value) && mb_strlen($value) < $parameter) {
                        return sprintf(__('This field must be at least %s characters.', 'bible-plugin'), $parameter);
                    } elseif (is_numeric($value) && $value < $parameter) {
                        return sprintf(__('This field must be at least %s.', 'bible-plugin'), $parameter);
                    }
                }
                break;

            case 'max':
                if ($value !== null) {
                    if (is_string($value) && mb_strlen($value) > $parameter) {
                        return sprintf(__('This field must not exceed %s characters.', 'bible-plugin'), $parameter);
                    } elseif (is_numeric($value) && $value > $parameter) {
                        return sprintf(__('This field must not exceed %s.', 'bible-plugin'), $parameter);
                    }
                }
                break;

            case 'in':
                if ($value !== null) {
                    $allowedValues = explode(',', $parameter);
                    if (!in_array($value, $allowedValues)) {
                        return __('This field contains an invalid value.', 'bible-plugin');
                    }
                }
                break;

            case 'boolean':
                if ($value !== null && !is_bool($value) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1') {
                    return __('This field must be a boolean.', 'bible-plugin');
                }
                break;

            case 'array':
                if ($value !== null && !is_array($value)) {
                    return __('This field must be an array.', 'bible-plugin');
                }
                break;
        }

        return true;
    }
}
