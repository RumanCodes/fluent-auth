<?php
/**
 * Mock classes for testing
 */

if (!class_exists('WP_Error')) {
    class WP_Error {
        public $errors = [];
        public $error_data = [];
        
        public function __construct($code = '', $message = '', $data = '') {
            if (empty($code)) {
                return;
            }
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
        
        public function get_error_codes() {
            if (empty($this->errors)) {
                return [];
            }
            return array_keys($this->errors);
        }
        
        public function get_error_code() {
            $codes = $this->get_error_codes();
            if (empty($codes)) {
                return '';
            }
            return $codes[0];
        }
        
        public function get_error_messages($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            if (isset($this->errors[$code])) {
                return $this->errors[$code];
            }
            return [];
        }
        
        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            $messages = $this->get_error_messages($code);
            if (empty($messages)) {
                return '';
            }
            return $messages[0];
        }
        
        public function get_error_data($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            if (isset($this->error_data[$code])) {
                return $this->error_data[$code];
            }
            return null;
        }
        
        public function has_errors() {
            return !empty($this->errors);
        }
        
        public function add($code, $message, $data = '') {
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
    }
}

if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        protected $params = [];
        
        public function __construct($method = '', $route = '') {
            // Mock constructor
        }
        
        public function get_param($key) {
            return $this->params[$key] ?? null;
        }
        
        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }
        
        public function get_params() {
            return $this->params;
        }
        
        public function set_params($params) {
            $this->params = $params;
        }
    }
}

if (!class_exists('WP_User')) {
    class WP_User {
        public $ID = 1;
        public $user_login = 'testuser';
        public $user_email = 'test@example.com';
        public $user_url = '';
        public $first_name = '';
        public $last_name = '';
        public $description = '';
        public $roles = ['subscriber'];
    }
}