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
     * @param RequestInterface|array $data_or_request The request to validate
     * @param array $rules The validation rules
     * @return array|bool True if validation passes, array of errors if it fails
     */
    public function validate( $data_or_request, array $rules )
    {
        $errors = [];
        $data = $data_or_request;

        if ( !is_array( $data ) && $data instanceof RequestInterface ) {
            return $this->validate_request( $data, $rules );
        }

        foreach ( $rules as $field => $rule_string ) {
            $field_rules = explode( '|', $rule_string );
            $value = is_array( $data ) ? ( $data[$field] ?? null ) : null;

            foreach ( $field_rules as $rule ) {
                $result = $this->validate_rule( $field, $value, $rule );
                if ( $result !== true ) {
                    $errors[$field] = $result;
                    break; // Stop validating this field once an error is found
                }
            }
        }

        return empty( $errors ) ? true : $errors;
    }


    /**
     * Validates a request based on a set of rules and returns validation results.
     *
     * @param RequestInterface $request The request object containing input data.
     * @param array $rules An associative array where keys are field names and values are validation rules.
     * @return bool|array Returns true if all validations pass, or an array of errors if any validations fail.
     */
    public function validate_request( RequestInterface $request, array $rules )
    {
        $errors = [];

        foreach ( $rules as $field => $rule_string ) {
            $field_rules = explode( '|', $rule_string );
            $value = $request->get( $field );

            foreach ( $field_rules as $rule ) {
                $result = $this->validate_rule( $field, $value, $rule );
                if ( $result !== true ) {
                    $errors[$field] = $result;
                    break; // Stop validating this field once an error is found
                }
            }
        }

        return empty( $errors ) ? true : $errors;
    }

    /**
     * Validate a single rule for a field.
     *
     * @param string $field The field name
     * @param mixed $value The field value
     * @param string $rule The rule to validate against
     * @return string|bool True if validation passes, error message if it fails
     */
    protected function validate_rule( string $field, $value, string $rule )
    {
        // Handle rules with parameters (e.g., min:3)
        if ( strpos( $rule, ':' ) !== false ) {
            list($rule_name, $parameter) = explode( ':', $rule, 2 );
        } else {
            $rule_name = $rule;
            $parameter = null;
        }

        switch ( $rule_name ) {
            case 'required':
                if ( $value === null || $value === '' ) {
                    return __( 'This field is required.', 'bible-plugin' );
                }
                break;

            case 'string':
                if ( $value !== null && !is_string( $value ) ) {
                    return __( 'This field must be a string.', 'bible-plugin' );
                }
                break;

            case 'numeric':
                if ( $value !== null && !is_numeric( $value ) ) {
                    return __( 'This field must be a number.', 'bible-plugin' );
                }
                break;

            case 'email':
                if ( $value !== null && !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                    return __( 'This field must be a valid email address.', 'bible-plugin' );
                }
                break;

            case 'url':
                if ( $value !== null && !filter_var( $value, FILTER_VALIDATE_URL ) ) {
                    return __( 'This field must be a valid URL.', 'bible-plugin' );
                }
                break;

            case 'min':
                if ( $value !== null ) {
                    if ( is_string( $value ) && mb_strlen( $value ) < $parameter ) {
                        return sprintf( __( 'This field must be at least %s characters.', 'bible-plugin' ), $parameter );
                    } elseif ( is_numeric( $value ) && $value < $parameter ) {
                        return sprintf( __( 'This field must be at least %s.', 'bible-plugin' ), $parameter );
                    }
                }
                break;

            case 'max':
                if ( $value !== null ) {
                    if ( is_string( $value ) && mb_strlen( $value ) > $parameter ) {
                        return sprintf( __( 'This field must not exceed %s characters.', 'bible-plugin' ), $parameter );
                    } elseif ( is_numeric( $value ) && $value > $parameter ) {
                        return sprintf( __( 'This field must not exceed %s.', 'bible-plugin' ), $parameter );
                    }
                }
                break;

            case 'in':
                if ( $value !== null ) {
                    $allowed_values = explode( ',', $parameter );
                    if ( !in_array( $value, $allowed_values ) ) {
                        return __( 'This field contains an invalid value.', 'bible-plugin' );
                    }
                }
                break;

            case 'boolean':
                if ( $value !== null && !is_bool( $value ) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1' ) {
                    return __( 'This field must be a boolean.', 'bible-plugin' );
                }
                break;

            case 'array':
                if ( $value !== null && !is_array( $value ) ) {
                    return __( 'This field must be an array.', 'bible-plugin' );
                }
                break;
        }

        return true;
    }
}
