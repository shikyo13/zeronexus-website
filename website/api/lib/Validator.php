<?php
/**
 * Input validation helper
 * 
 * This class provides common validation methods for API input,
 * ensuring consistent validation across endpoints.
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = null) {
        $this->data = $data ?? $_REQUEST;
    }
    
    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "The {$field} field is required.";
        }
        return $this;
    }
    
    /**
     * Validate URL format
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function url($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = $message ?? "The {$field} field must be a valid URL.";
        }
        return $this;
    }
    
    /**
     * Validate domain name
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function domain($field, $message = null) {
        if (isset($this->data[$field])) {
            $domain = $this->data[$field];
            $pattern = '/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)*[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$/';
            
            if (!preg_match($pattern, $domain)) {
                $this->errors[$field] = $message ?? "The {$field} field must be a valid domain name.";
            }
        }
        return $this;
    }
    
    /**
     * Validate IP address
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function ip($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_IP)) {
            $this->errors[$field] = $message ?? "The {$field} field must be a valid IP address.";
        }
        return $this;
    }
    
    /**
     * Validate value is in list
     * 
     * @param string $field Field name
     * @param array $values Allowed values
     * @param string $message Custom error message
     * @return self
     */
    public function in($field, array $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?? "The {$field} field must be one of: " . implode(', ', $values);
        }
        return $this;
    }
    
    /**
     * Validate integer value
     * 
     * @param string $field Field name
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param string $message Custom error message
     * @return self
     */
    public function integer($field, $min = null, $max = null, $message = null) {
        if (isset($this->data[$field])) {
            $value = $this->data[$field];
            
            if (!is_numeric($value) || (int)$value != $value) {
                $this->errors[$field] = $message ?? "The {$field} field must be an integer.";
            } else {
                $intValue = (int)$value;
                
                if ($min !== null && $intValue < $min) {
                    $this->errors[$field] = "The {$field} field must be at least {$min}.";
                }
                
                if ($max !== null && $intValue > $max) {
                    $this->errors[$field] = "The {$field} field must not exceed {$max}.";
                }
            }
        }
        return $this;
    }
    
    /**
     * Validate string length
     * 
     * @param string $field Field name
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @param string $message Custom error message
     * @return self
     */
    public function length($field, $min = null, $max = null, $message = null) {
        if (isset($this->data[$field])) {
            $length = strlen($this->data[$field]);
            
            if ($min !== null && $length < $min) {
                $this->errors[$field] = "The {$field} field must be at least {$min} characters.";
            }
            
            if ($max !== null && $length > $max) {
                $this->errors[$field] = "The {$field} field must not exceed {$max} characters.";
            }
        }
        return $this;
    }
    
    /**
     * Validate CVE ID format
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function cveId($field, $message = null) {
        if (isset($this->data[$field])) {
            $pattern = '/^CVE-\d{4}-\d{4,}$/';
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field] = $message ?? "The {$field} field must be a valid CVE ID (e.g., CVE-2023-12345).";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get specific field value
     * 
     * @param string $field Field name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($field, $default = null) {
        return $this->data[$field] ?? $default;
    }
    
    /**
     * Get all validated data
     * 
     * @return array
     */
    public function all() {
        return $this->data;
    }
    
    /**
     * Static method for quick validation
     * 
     * @param array $rules Validation rules
     * @return self
     */
    public static function make(array $rules = []) {
        $validator = new self();
        
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $validator->$rule($field);
                } elseif (is_array($rule)) {
                    $method = array_shift($rule);
                    $validator->$method($field, ...$rule);
                }
            }
        }
        
        return $validator;
    }
}