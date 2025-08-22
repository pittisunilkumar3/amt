<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load database first
        $this->load->database();

        // Load models
        $this->load->model('teacher_auth_model');
        $this->load->model('staff_model');
        $this->load->model('setting_model');

        // Load helpers
        $this->load->helper('json_output');
    }

    /**
     * Test endpoint to check if controller is working
     * GET /teacher/test
     */
    public function test()
    {
        // Test model loading
        $models_loaded = array(
            'teacher_auth_model' => isset($this->teacher_auth_model),
            'staff_model' => isset($this->staff_model),
            'setting_model' => isset($this->setting_model)
        );

        json_output(200, array(
            'status' => 1,
            'message' => 'Teacher Auth Controller is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'database_connected' => $this->db->conn_id ? true : false,
            'models_loaded' => $models_loaded
        ));
    }

    /**
     * Simple login test without JWT
     * POST /teacher/simple-login
     */
    public function simple_login()
    {
        // Check authentication headers
        $client_service = $this->input->get_request_header('Client-Service', true);
        $auth_key = $this->input->get_request_header('Auth-Key', true);

        if ($client_service != 'smartschool' || $auth_key != 'schoolAdmin@') {
            json_output(401, array('status' => 0, 'message' => 'Unauthorized access.'));
            return;
        }

        // Get POST data
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (empty($email) || empty($password)) {
            json_output(400, array('status' => 0, 'message' => 'Email and password are required.'));
            return;
        }

        // Simple database check
        $this->db->select('id, email, name, surname, is_active');
        $this->db->from('staff');
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->where('is_active', 1);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $staff = $query->row();
            json_output(200, array(
                'status' => 1,
                'message' => 'Login successful',
                'staff_id' => $staff->id,
                'name' => $staff->name . ' ' . $staff->surname,
                'email' => $staff->email
            ));
        } else {
            json_output(401, array('status' => 0, 'message' => 'Invalid email or password.'));
        }
    }

    /**
     * Check if specific teacher credentials exist in database
     * POST /teacher/check-credentials
     */
    public function check_credentials()
    {
        // Check authentication headers
        $client_service = $this->input->get_request_header('Client-Service', true);
        $auth_key = $this->input->get_request_header('Auth-Key', true);

        if ($client_service != 'smartschool' || $auth_key != 'schoolAdmin@') {
            json_output(401, array('status' => 0, 'message' => 'Unauthorized access - Invalid headers.'));
            return;
        }

        // Get POST data
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (empty($email)) {
            json_output(400, array('status' => 0, 'message' => 'Email is required.'));
            return;
        }

        // Check if email exists
        $this->db->select('id, email, name, surname, password, is_active, designation, department');
        $this->db->from('staff');
        $this->db->where('email', $email);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            json_output(404, array(
                'status' => 0,
                'message' => 'Email not found in database.',
                'email_searched' => $email
            ));
            return;
        }

        $staff = $query->row();

        // Check password if provided
        $password_match = false;
        if (!empty($password)) {
            $password_match = ($staff->password === $password);
        }

        json_output(200, array(
            'status' => 1,
            'message' => 'Teacher record found',
            'data' => array(
                'staff_id' => $staff->id,
                'email' => $staff->email,
                'name' => $staff->name . ' ' . $staff->surname,
                'is_active' => $staff->is_active,
                'designation' => $staff->designation,
                'department' => $staff->department,
                'password_provided' => !empty($password),
                'password_match' => $password_match,
                'stored_password_length' => strlen($staff->password)
            )
        ));
    }

    /**
     * Debug version of teacher login
     * POST /teacher/debug-login
     */
    public function debug_login()
    {
        // Get all headers for debugging
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace('HTTP_', '', $key);
                $header_name = str_replace('_', '-', $header_name);
                $headers[$header_name] = $value;
            }
        }

        // Get specific headers
        $client_service = $this->input->get_request_header('Client-Service', true);
        $auth_key = $this->input->get_request_header('Auth-Key', true);

        // Get POST data
        $post_data = $this->input->post();

        // Check authentication headers first
        $auth_check = $this->teacher_auth_model->check_auth_client();

        $debug_info = array(
            'status' => 1,
            'message' => 'Debug information',
            'debug' => array(
                'all_headers' => $headers,
                'client_service' => $client_service,
                'auth_key' => $auth_key,
                'expected_client_service' => 'smartschool',
                'expected_auth_key' => 'schoolAdmin@',
                'headers_valid' => ($client_service == 'smartschool' && $auth_key == 'schoolAdmin@'),
                'post_data' => $post_data,
                'auth_check_result' => $auth_check,
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'content_type' => $this->input->get_request_header('Content-Type', true)
            )
        );

        // If headers are valid, try the actual login
        if ($client_service == 'smartschool' && $auth_key == 'schoolAdmin@') {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            if (!empty($email) && !empty($password)) {
                try {
                    $login_result = $this->teacher_auth_model->login($email, $password, '');
                    $debug_info['debug']['login_attempt'] = array(
                        'email' => $email,
                        'password_length' => strlen($password),
                        'login_result' => $login_result
                    );
                } catch (Exception $e) {
                    $debug_info['debug']['login_error'] = $e->getMessage();
                }
            }
        }

        json_output(200, $debug_info);
    }

    /**
     * Teacher Login
     * POST /teacher/login
     */
    public function login()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $params = json_decode(file_get_contents('php://input'), true);

                // Validate required parameters
                if (!isset($params['email']) || !isset($params['password'])) {
                    json_output(400, array('status' => 400, 'message' => 'Email and password are required.'));
                    return;
                }

                $email = $params['email'];
                $password = $params['password'];
                $app_key = isset($params['deviceToken']) ? $params['deviceToken'] : null;

                $response = $this->teacher_auth_model->login($email, $password, $app_key);
                json_output(200, $response);
            } else {
                json_output(401, array('status' => 0, 'message' => 'Unauthorized. Please check Client-Service and Auth-Key headers.'));
            }
        }
    }

    /**
     * Teacher Logout
     * POST /teacher/logout
     */
    public function logout()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $deviceToken = isset($params['deviceToken']) ? $params['deviceToken'] : null;
                    $response = $this->teacher_auth_model->logout($deviceToken);
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Profile
     * GET /teacher/profile
     */
    public function profile()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $response = $this->teacher_auth_model->get_profile();
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Update Teacher Profile
     * PUT /teacher/profile
     */
    public function update_profile()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'PUT') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $response = $this->teacher_auth_model->update_profile($params);
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Change Teacher Password
     * PUT /teacher/change-password
     */
    public function change_password()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'PUT') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    
                    // Validate required parameters
                    if (!isset($params['current_password']) || !isset($params['new_password'])) {
                        json_output(400, array('status' => 400, 'message' => 'Current password and new password are required.'));
                    }
                    
                    $response = $this->teacher_auth_model->change_password($params['current_password'], $params['new_password']);
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Dashboard Data
     * GET /teacher/dashboard
     */
    public function dashboard()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $response = $this->teacher_auth_model->get_dashboard_data();
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Refresh JWT Token
     * POST /teacher/refresh-token
     */
    public function refresh_token()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $params = json_decode(file_get_contents('php://input'), true);

                if (!isset($params['jwt_token'])) {
                    json_output(400, array('status' => 400, 'message' => 'JWT token is required.'));
                }

                $response = $this->teacher_auth_model->refresh_jwt_token($params['jwt_token']);
                json_output(200, $response);
            }
        }
    }

    /**
     * Validate JWT Token
     * POST /teacher/validate-token
     */
    public function validate_token()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $params = json_decode(file_get_contents('php://input'), true);

                if (!isset($params['jwt_token'])) {
                    json_output(400, array('status' => 400, 'message' => 'JWT token is required.'));
                }

                $response = $this->teacher_auth_model->validate_jwt_token($params['jwt_token']);
                json_output(200, $response);
            }
        }
    }
}
