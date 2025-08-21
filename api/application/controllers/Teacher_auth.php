<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('teacher_auth_model');
        $this->load->model('staff_model');
        $this->load->model('setting_model');
        $this->load->library('teacher_middleware');
        $this->load->helper('teacher_auth');

        // Apply middleware to protected methods
        $method = $this->router->fetch_method();
        if (in_array($method, ['profile', 'update_profile', 'change_password', 'dashboard', 'logout'])) {
            $this->teacher_middleware->check_auth();
        }
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
                }
                
                $email = $params['email'];
                $password = $params['password'];
                $app_key = isset($params['deviceToken']) ? $params['deviceToken'] : null;
                
                $response = $this->teacher_auth_model->login($email, $password, $app_key);
                json_output(200, $response);
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
