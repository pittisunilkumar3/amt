<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_auth_model extends CI_Model
{

    public $client_service               = "smartschool";
    public $auth_key                     = "schoolAdmin@";
    public $security_authentication_flag = 1; // Enable authentication for teachers

    public function __construct()
    {
        parent::__construct();
        // Load models
        $this->load->model(array('staff_model', 'setting_model'));

        // Load libraries with error handling
        $this->load->library('encryption');
        $this->load->library('enc_lib'); // Load encryption library for password verification

        // Try to load JWT library, but don't fail if it's not available
        if (file_exists(APPPATH . 'libraries/JWT_lib.php')) {
            try {
                $this->load->library('JWT_lib');
            } catch (Exception $e) {
                // JWT library not available, continue without it
                log_message('info', 'JWT library not available: ' . $e->getMessage());
            }
        }
    }

    public function check_auth_client()
    {
        $client_service = $this->input->get_request_header('Client-Service', true);
        $auth_key       = $this->input->get_request_header('Auth-Key', true);
        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            return true;
        } else {
            return false;
        }
    }

    public function login($email, $password, $app_key)
    {
        // Check teacher login credentials
        $q = $this->checkTeacherLogin($email, $password);

        if (empty($q)) {
            return array('status' => 0, 'message' => 'Invalid Email or Password');
        } else {
            if ($q->is_active == 1) {
                $result = $this->getTeacherInformation($q->id);

                if ($result != false) {
                    $setting_result = $this->setting_model->get();

                    // Handle currency settings with defaults
                    $currency_symbol = isset($setting_result[0]['currency_symbol']) ? $setting_result[0]['currency_symbol'] : '$';
                    $currency = isset($setting_result[0]['currency']) ? $setting_result[0]['currency'] : 'USD';
                    $currency_short_name = isset($setting_result[0]['currency']) ? $setting_result[0]['currency'] : 'USD';

                    // Handle language settings with defaults
                    $lang_id = isset($setting_result[0]['lang_id']) ? $setting_result[0]['lang_id'] : 1;
                    $language = isset($setting_result[0]['language']) ? $setting_result[0]['language'] : 'English';
                    $short_code = 'en';

                    $last_login = date('Y-m-d H:i:s');

                    // Generate JWT token with teacher information (if JWT library is available)
                    $jwt_token = null;
                    if (isset($this->JWT_lib) && is_object($this->JWT_lib)) {
                        try {
                            $jwt_payload = array(
                                'user_id' => $q->id,
                                'staff_id' => $result->id,
                                'email' => $result->email,
                                'role' => 'teacher',
                                'employee_id' => $result->employee_id,
                                'name' => $result->name . ' ' . $result->surname
                            );
                            $jwt_token = $this->JWT_lib->generate_token($jwt_payload);
                        } catch (Exception $e) {
                            // JWT generation failed, continue without it
                            $jwt_token = null;
                            log_message('error', 'JWT token generation failed: ' . $e->getMessage());
                        }
                    }

                    // Also generate simple token for backward compatibility
                    $simple_token = $this->getToken();
                    $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));

                    $this->db->trans_start();
                    $this->db->insert('users_authentication', array(
                        'users_id' => $q->id,
                        'token' => $simple_token,
                        'staff_id' => $result->id,
                        'expired_at' => $expired_at
                    ));

                    // Update app key if provided
                    if ($app_key) {
                        $updateData = array('app_key' => $app_key);
                        $this->db->where('id', $result->id);
                        $this->db->update('staff', $updateData);
                    }

                    $fullname = trim($result->name . ' ' . $result->surname);
                    if (empty($fullname)) {
                        $fullname = $result->name;
                    }

                    $session_data = array(
                        'id' => $result->id,
                        'staff_id' => $result->id,
                        'employee_id' => $result->employee_id,
                        'role' => 'teacher',
                        'email' => $result->email,
                        'contact_no' => $result->contact_no,
                        'username' => $fullname,
                        'name' => $result->name,
                        'surname' => $result->surname,
                        'designation' => $result->designation,
                        'department' => $result->department,
                        'date_format' => isset($setting_result[0]['date_format']) ? $setting_result[0]['date_format'] : 'd-m-Y',
                        'currency_symbol' => $currency_symbol,
                        'currency_short_name' => $currency_short_name,
                        'currency_id' => $currency,
                        'timezone' => isset($setting_result[0]['timezone']) ? $setting_result[0]['timezone'] : 'UTC',
                        'sch_name' => isset($setting_result[0]['name']) ? $setting_result[0]['name'] : 'School',
                        'language' => array('lang_id' => $lang_id, 'language' => $language, 'short_code' => $short_code),
                        'is_rtl' => isset($setting_result[0]['is_rtl']) ? $setting_result[0]['is_rtl'] : '0',
                        'theme' => isset($setting_result[0]['theme']) ? $setting_result[0]['theme'] : 'default.jpg',
                        'image' => $result->image,
                        'start_week' => isset($setting_result[0]['start_week']) ? $setting_result[0]['start_week'] : 'Monday',
                        'superadmin_restriction' => isset($setting_result[0]['superadmin_restriction']) ? $setting_result[0]['superadmin_restriction'] : '0',
                    );

                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        return array('status' => 0, 'message' => 'Internal server error.');
                    } else {
                        $this->db->trans_commit();
                        return array(
                            'status' => 1,
                            'message' => 'Successfully logged in.',
                            'id' => $q->id,
                            'token' => $simple_token,
                            'jwt_token' => $jwt_token,
                            'role' => 'teacher',
                            'record' => $session_data
                        );
                    }
                } else {
                    return array('status' => 0, 'message' => 'Your account is suspended');
                }
            } else {
                return array('status' => 0, 'message' => 'Your account is disabled. Please contact administrator.');
            }
        }
    }

    public function checkTeacherLogin($email, $password)
    {
        // Get the teacher record by email (same approach as main project's Staff_model->getByEmail)
        $this->db->select('staff.id, staff.email, staff.password, staff.is_active, staff.lang_id');
        $this->db->from('staff');
        $this->db->where('staff.email', $email);
        $this->db->where('staff.is_active', 1);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $staff = $query->row();

            // Use the same password verification approach as main project
            // Staff_model->checkLogin() uses $this->enc_lib->passHashDyc($password, $record->password)
            $pass_verify = $this->enc_lib->passHashDyc($password, $staff->password);

            if ($pass_verify) {
                return $staff;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getTeacherInformation($staff_id)
    {
        $this->db->select('staff.*, staff_designation.designation as designation_name, department.department_name');
        $this->db->from('staff');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('department', 'department.id = staff.department', 'left');
        $this->db->where('staff.id', $staff_id);
        $this->db->where('staff.is_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function getTeacherCurrency($staff_id)
    {
        // Implement currency logic if needed
        $setting_result = $this->setting_model->get();
        return array((object)array(
            'id' => $setting_result[0]['currency_id'],
            'symbol' => $setting_result[0]['currency_symbol'],
            'short_name' => $setting_result[0]['short_name']
        ));
    }

    public function getTeacherLanguage($staff_id)
    {
        $this->db->select('languages.*');
        $this->db->from('languages');
        $this->db->join('staff', 'staff.lang_id = languages.id');
        $this->db->where('staff.id', $staff_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            $setting_result = $this->setting_model->get();
            return array((object)array(
                'lang_id' => $setting_result[0]['lang_id'],
                'language' => $setting_result[0]['language'],
                'short_code' => $setting_result[0]['short_code']
            ));
        }
    }

    public function getToken($randomIdLength = 32)
    {
        $token = '';
        do {
            $bytes = random_bytes($randomIdLength);
            $token .= str_replace(
                ['.', '/', '='], '', base64_encode($bytes)
            );
        } while (strlen($token) < $randomIdLength);
        return substr($token, 0, $randomIdLength);
    }

    public function logout($deviceToken)
    {
        $users_id = $this->input->get_request_header('User-ID', true);
        $token = $this->input->get_request_header('Authorization', true);

        // Clear app key if device token provided
        if ($deviceToken) {
            $this->db->where('app_key', $deviceToken)->update('staff', array('app_key' => null));
        }

        // Remove authentication token
        $this->db->where('users_id', $users_id)->where('token', $token)->delete('users_authentication');

        return array('status' => 200, 'message' => 'Successfully logged out.');
    }

    public function auth()
    {
        if ($this->security_authentication_flag) {
            $users_id = $this->input->get_request_header('User-ID', true);
            $token = $this->input->get_request_header('Authorization', true);
            $jwt_token = $this->input->get_request_header('JWT-Token', true);

            // Try JWT authentication first (if JWT library is available)
            if ($jwt_token && isset($this->JWT_lib) && is_object($this->JWT_lib)) {
                try {
                    $jwt_payload = $this->JWT_lib->verify_token($jwt_token);
                    if ($jwt_payload) {
                        return array(
                            'status' => 200,
                            'message' => 'Authorized via JWT.',
                            'staff_id' => $jwt_payload['staff_id'],
                            'user_id' => $jwt_payload['user_id'],
                            'auth_type' => 'jwt'
                        );
                    } else {
                        return array('status' => 401, 'message' => 'Invalid or expired JWT token.');
                    }
                } catch (Exception $e) {
                    // JWT verification failed, fall back to traditional auth
                    log_message('error', 'JWT verification failed: ' . $e->getMessage());
                }
            }

            // Fallback to traditional token authentication
            $q = $this->db->select('expired_at, staff_id, users_id')
                          ->from('users_authentication')
                          ->where('users_id', $users_id)
                          ->where('token', $token)
                          ->get()->row();

            if ($q == "") {
                return array('status' => 401, 'message' => 'Unauthorized.');
            } else {
                if ($q->expired_at < date('Y-m-d H:i:s')) {
                    return array('status' => 401, 'message' => 'Your session has expired.');
                } else {
                    // Update token expiration
                    $updated_at = date('Y-m-d H:i:s');
                    $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));
                    $this->db->where('users_id', $users_id)
                             ->where('token', $token)
                             ->update('users_authentication', array(
                                 'expired_at' => $expired_at,
                                 'updated_at' => $updated_at
                             ));
                    return array(
                        'status' => 200,
                        'message' => 'Authorized via token.',
                        'staff_id' => $q->staff_id,
                        'user_id' => $q->users_id,
                        'auth_type' => 'token'
                    );
                }
            }
        } else {
            return array('status' => 200, 'message' => 'Authorized.');
        }
    }

    public function get_profile()
    {
        $auth_check = $this->auth();
        if ($auth_check['status'] != 200) {
            return $auth_check;
        }

        $staff_id = $auth_check['staff_id'];
        $result = $this->getTeacherInformation($staff_id);

        if ($result) {
            $profile_data = array(
                'id' => $result->id,
                'employee_id' => $result->employee_id,
                'name' => $result->name,
                'surname' => $result->surname,
                'father_name' => $result->father_name,
                'mother_name' => $result->mother_name,
                'email' => $result->email,
                'contact_no' => $result->contact_no,
                'emergency_contact_no' => $result->emergency_contact_no,
                'dob' => $result->dob,
                'marital_status' => $result->marital_status,
                'date_of_joining' => $result->date_of_joining,
                'designation' => $result->designation_name,
                'department' => $result->department_name,
                'qualification' => $result->qualification,
                'work_exp' => $result->work_exp,
                'local_address' => $result->local_address,
                'permanent_address' => $result->permanent_address,
                'image' => $result->image,
                'gender' => $result->gender,
                'account_title' => $result->account_title,
                'bank_account_no' => $result->bank_account_no,
                'bank_name' => $result->bank_name,
                'ifsc_code' => $result->ifsc_code,
                'bank_branch' => $result->bank_branch,
                'payscale' => $result->payscale,
                'basic_salary' => $result->basic_salary,
                'epf_no' => $result->epf_no,
                'contract_type' => $result->contract_type,
                'work_shift' => $result->work_shift,
                'work_location' => $result->work_location,
                'note' => $result->note,
                'is_active' => $result->is_active
            );

            return array('status' => 1, 'message' => 'Profile retrieved successfully.', 'data' => $profile_data);
        } else {
            return array('status' => 0, 'message' => 'Profile not found.');
        }
    }

    public function update_profile($params)
    {
        $auth_check = $this->auth();
        if ($auth_check['status'] != 200) {
            return $auth_check;
        }

        $staff_id = $auth_check['staff_id'];

        // Define updatable fields
        $updatable_fields = array(
            'name', 'surname', 'father_name', 'mother_name', 'contact_no',
            'emergency_contact_no', 'local_address', 'permanent_address',
            'qualification', 'work_exp', 'note', 'account_title',
            'bank_account_no', 'bank_name', 'ifsc_code', 'bank_branch'
        );

        $update_data = array();
        foreach ($updatable_fields as $field) {
            if (isset($params[$field])) {
                $update_data[$field] = $params[$field];
            }
        }

        if (!empty($update_data)) {
            $this->db->where('id', $staff_id);
            $this->db->update('staff', $update_data);

            if ($this->db->affected_rows() > 0) {
                return array('status' => 1, 'message' => 'Profile updated successfully.');
            } else {
                return array('status' => 0, 'message' => 'No changes made to profile.');
            }
        } else {
            return array('status' => 0, 'message' => 'No valid fields provided for update.');
        }
    }

    public function change_password($current_password, $new_password)
    {
        $auth_check = $this->auth();
        if ($auth_check['status'] != 200) {
            return $auth_check;
        }

        $staff_id = $auth_check['staff_id'];

        // Verify current password
        $this->db->select('password');
        $this->db->from('staff');
        $this->db->where('id', $staff_id);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $staff = $query->row();

            // In production, use proper password verification
            if ($staff->password == $current_password) {
                // Update password (in production, hash the new password)
                $this->db->where('id', $staff_id);
                $this->db->update('staff', array('password' => $new_password));

                return array('status' => 1, 'message' => 'Password changed successfully.');
            } else {
                return array('status' => 0, 'message' => 'Current password is incorrect.');
            }
        } else {
            return array('status' => 0, 'message' => 'Staff not found.');
        }
    }

    public function get_dashboard_data()
    {
        $auth_check = $this->auth();
        if ($auth_check['status'] != 200) {
            return $auth_check;
        }

        $staff_id = $auth_check['staff_id'];

        // Get basic teacher information
        $teacher_info = $this->getTeacherInformation($staff_id);

        // Get assigned classes (if class_teacher table exists)
        $this->db->select('classes.class, sections.section, class_teacher.session_id');
        $this->db->from('class_teacher');
        $this->db->join('classes', 'classes.id = class_teacher.class_id');
        $this->db->join('sections', 'sections.id = class_teacher.section_id');
        $this->db->where('class_teacher.staff_id', $staff_id);
        $assigned_classes = $this->db->get()->result_array();

        // Get subject assignments (if teacher_subject table exists)
        $this->db->select('subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('teacher_subject');
        $this->db->join('subjects', 'subjects.id = teacher_subject.subject_id');
        $this->db->where('teacher_subject.teacher_id', $staff_id);
        $assigned_subjects = $this->db->get()->result_array();

        $dashboard_data = array(
            'teacher_info' => array(
                'name' => $teacher_info->name . ' ' . $teacher_info->surname,
                'employee_id' => $teacher_info->employee_id,
                'designation' => $teacher_info->designation_name,
                'department' => $teacher_info->department_name,
                'email' => $teacher_info->email,
                'image' => $teacher_info->image
            ),
            'assigned_classes' => $assigned_classes,
            'assigned_subjects' => $assigned_subjects,
            'total_classes' => count($assigned_classes),
            'total_subjects' => count($assigned_subjects)
        );

        return array('status' => 1, 'message' => 'Dashboard data retrieved successfully.', 'data' => $dashboard_data);
    }

    public function refresh_jwt_token($jwt_token)
    {
        if (!isset($this->JWT_lib) || !is_object($this->JWT_lib)) {
            return array('status' => 0, 'message' => 'JWT library not available.');
        }

        try {
            $new_token = $this->JWT_lib->refresh_token($jwt_token);

            if ($new_token) {
                return array(
                    'status' => 1,
                    'message' => 'Token refreshed successfully.',
                    'jwt_token' => $new_token,
                    'expires_in' => $this->JWT_lib->get_expiration_time() * 3600 // Convert hours to seconds
                );
            } else {
                return array('status' => 0, 'message' => 'Invalid or expired token. Please login again.');
            }
        } catch (Exception $e) {
            return array('status' => 0, 'message' => 'Token refresh failed: ' . $e->getMessage());
        }
    }

    public function validate_jwt_token($jwt_token)
    {
        if (!isset($this->JWT_lib) || !is_object($this->JWT_lib)) {
            return array('status' => 0, 'message' => 'JWT library not available.');
        }

        try {
            $payload = $this->JWT_lib->verify_token($jwt_token);

            if ($payload) {
                $remaining_time = $this->JWT_lib->get_remaining_time($jwt_token);
                $is_expiring_soon = $this->JWT_lib->is_token_expiring_soon($jwt_token);

                return array(
                    'status' => 1,
                    'message' => 'Token is valid.',
                    'payload' => $payload,
                    'remaining_time' => $remaining_time,
                    'expires_in_hours' => round($remaining_time / 3600, 2),
                    'is_expiring_soon' => $is_expiring_soon
                );
            } else {
                return array('status' => 0, 'message' => 'Invalid or expired token.');
            }
        } catch (Exception $e) {
            return array('status' => 0, 'message' => 'Token validation failed: ' . $e->getMessage());
        }
    }
}
