<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_webservice extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Start output buffering if not already started
        if (!ob_get_level()) {
            ob_start();
        }

        // Set JSON content type early
        $this->output->set_content_type('application/json');

        // Load essential models first
        $this->load->model('teacher_auth_model');
        $this->load->helper('json_output');

        // Load other models with error handling
        try {
            $this->load->model(array(
                'teacher_permission_model', 'staff_model', 'setting_model', 'rolepermission_model'
            ));
        } catch (Exception $e) {
            log_message('error', 'Error loading models: ' . $e->getMessage());
        }

        // Load libraries with error handling
        try {
            $this->load->library('teacher_middleware');
        } catch (Exception $e) {
            log_message('error', 'Teacher middleware not available: ' . $e->getMessage());
        }

        try {
            $this->load->library('customlib');
        } catch (Exception $e) {
            log_message('error', 'Customlib not available: ' . $e->getMessage());
        }

        try {
            $this->load->helper('teacher_auth');
        } catch (Exception $e) {
            log_message('error', 'Teacher auth helper not available: ' . $e->getMessage());
        }

        // Set timezone with error handling
        try {
            if (isset($this->setting_model)) {
                $setting = $this->setting_model->getSchoolDetail();
                if ($setting && isset($setting->timezone) && $setting->timezone != "") {
                    date_default_timezone_set($setting->timezone);
                } else {
                    date_default_timezone_set('UTC');
                }
            } else {
                date_default_timezone_set('UTC');
            }
        } catch (Exception $e) {
            log_message('error', 'Error setting timezone: ' . $e->getMessage());
            date_default_timezone_set('UTC');
        }

        // Set custom error handling for JSON responses
        set_error_handler(array($this, 'custom_error_handler'));
        set_exception_handler(array($this, 'custom_exception_handler'));
    }

    /**
     * Custom error handler for JSON responses
     */
    public function custom_error_handler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $error_response = array(
            'status' => 0,
            'message' => 'PHP Error occurred',
            'error' => array(
                'type' => 'PHP Error',
                'severity' => $severity,
                'message' => $message,
                'file' => basename($file),
                'line' => $line
            ),
            'timestamp' => date('Y-m-d H:i:s')
        );

        // Log the error
        log_message('error', "PHP Error: $message in $file on line $line");

        // Only send JSON error for database or critical errors
        if (stripos($message, 'database') !== false || 
            stripos($message, 'fatal') !== false ||
            stripos($message, 'call to') !== false) {
            
            if (ob_get_level()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error_response);
            exit;
        }

        return false;
    }

    /**
     * Custom exception handler for JSON responses
     */
    public function custom_exception_handler($exception)
    {
        $error_response = array(
            'status' => 0,
            'message' => 'Exception occurred',
            'error' => array(
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine()
            ),
            'timestamp' => date('Y-m-d H:i:s')
        );

        // Log the exception
        log_message('error', "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());

        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode($error_response);
        exit;
    }

    /**
     * Get Teacher Menu Items
     * POST /teacher/menu
     * Body: {"staff_id": 123}
     */
    public function menu()
    {
        try {
            $method = $this->input->server('REQUEST_METHOD');

            if ($method != 'POST') {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'Bad request. Only POST method allowed.',
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Get JSON input
                $json_input = json_decode($this->input->raw_input_stream, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'Invalid JSON format in request body',
                    'error' => json_last_error_msg(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }
            
            if (empty($json_input) || !isset($json_input['staff_id'])) {
                json_output(400, array(
                    'status' => 0, 
                    'message' => 'staff_id is required in request body',
                    'example' => array('staff_id' => 123),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            $staff_id = intval($json_input['staff_id']);

            if ($staff_id <= 0) {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'staff_id must be a valid positive integer',
                    'provided' => $json_input['staff_id'],
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Check database connection
            if (!$this->db->conn_id) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Database connection failed',
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Get staff info directly from database (same logic as simple_menu)
            $this->db->select('s.*, r.name as role_name, r.is_superadmin, r.id as role_id');
            $this->db->from('staff s');
            $this->db->join('staff_roles sr', 'sr.staff_id = s.id', 'left');
            $this->db->join('roles r', 'r.id = sr.role_id', 'left');
            $this->db->where('s.id', $staff_id);
            $this->db->where('s.is_active', 1);
            
            $query = $this->db->get();
            
            if (!$query) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Database query failed',
                    'error' => $this->db->error(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }
            
            $staff_info = $query->row();

            if (!$staff_info) {
                json_output(404, array(
                    'status' => 0,
                    'message' => 'Staff member not found or inactive',
                    'staff_id' => $staff_id,
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Check if superadmin
            $is_superadmin = ($staff_info->role_id == 7 || $staff_info->is_superadmin == 1);

            // Get ALL menus (we'll filter by access_permissions)
            $this->db->select('*');
            $this->db->from('sidebar_menus');
            $this->db->where('is_active', 1);
            $this->db->where('sidebar_display', 1);
            $this->db->order_by('level');
            $menu_query = $this->db->get();

            if (!$menu_query) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Failed to fetch menus',
                    'error' => $this->db->error(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            $all_menus = $menu_query->result_array();

            // Get ALL submenus
            $this->db->select('*');
            $this->db->from('sidebar_sub_menus');
            $this->db->where('is_active', 1);
            $this->db->order_by('sidebar_menu_id, level');
            $submenu_query = $this->db->get();
            $all_submenus = $submenu_query ? $submenu_query->result_array() : array();

            // Group submenus by menu_id
            $submenus_by_menu = array();
            foreach ($all_submenus as $submenu) {
                $submenus_by_menu[$submenu['sidebar_menu_id']][] = $submenu;
            }

            // Filter menus and submenus using access_permissions (like admin dashboard)
            $menus = array();
            foreach ($all_menus as $menu) {
                // Check menu permission using access_permissions field
                $module_permission = $this->access_permission_sidebar_remove_pipe($menu['access_permissions']);
                $module_access = false;

                if ($is_superadmin) {
                    $module_access = true;
                } elseif (!empty($module_permission)) {
                    foreach ($module_permission as $m_permission_value) {
                        $cat_permission = $this->access_permission_remove_comma($m_permission_value);

                        if (count($cat_permission) >= 2) {
                            if ($this->hasPrivilege($staff_info->role_id, $staff_info->role_name, $cat_permission[0], $cat_permission[1])) {
                                $module_access = true;
                                break;
                            }
                        }
                    }
                }

                if ($module_access) {
                    // Filter submenus for this menu
                    $menu['submenus'] = array();

                    if (isset($submenus_by_menu[$menu['id']])) {
                        foreach ($submenus_by_menu[$menu['id']] as $submenu) {
                            $sidebar_permission = $this->access_permission_sidebar_remove_pipe($submenu['access_permissions']);
                            $sidebar_access = false;

                            if ($is_superadmin) {
                                $sidebar_access = true;
                            } elseif (!empty($sidebar_permission)) {
                                foreach ($sidebar_permission as $sidebar_permission_value) {
                                    $sidebar_cat_permission = $this->access_permission_remove_comma($sidebar_permission_value);

                                    if (count($sidebar_cat_permission) >= 2) {
                                        if ($this->hasPrivilege($staff_info->role_id, $staff_info->role_name, $sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                            $sidebar_access = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($sidebar_access) {
                                $menu['submenus'][] = $submenu;
                            }
                        }
                    }

                    $menus[] = $menu;
                }
            }

            // Enhanced response (keeping original format for compatibility)
            $response = array(
                'status' => 1,
                'message' => 'Menu items retrieved successfully.',
                'data' => array(
                    'staff_id' => $staff_id,
                    'staff_info' => array(
                        'id' => (int)$staff_info->id,
                        'name' => $staff_info->name,
                        'surname' => $staff_info->surname,
                        'employee_id' => $staff_info->employee_id,
                        'full_name' => trim($staff_info->name . ' ' . $staff_info->surname)
                    ),
                    'role' => array(
                        'id' => $staff_info->role_id ? (int)$staff_info->role_id : null,
                        'name' => $staff_info->role_name ? $staff_info->role_name : 'No Role Assigned',
                        'slug' => null, // Keeping original format
                        'is_superadmin' => $is_superadmin
                    ),
                    'menus' => $menus,
                    'total_menus' => count($menus),
                    'timestamp' => date('Y-m-d H:i:s')
                )
            );
            
            json_output(200, $response);
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 0,
                'message' => 'Exception occurred while retrieving menu items',
                'error' => array(
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ),
                'staff_id' => isset($staff_id) ? $staff_id : null,
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            log_message('error', 'Menu Exception: ' . $e->getMessage());
            json_output(500, $error_response);
        }
    }

    /**
     * Get Teacher Permissions
     * GET /teacher/permissions
     */
    public function permissions()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    
                    $permissions = $this->teacher_permission_model->getTeacherPermissions($staff_id);
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    
                    // Count total permissions
                    $total_permissions = 0;
                    $active_permissions = 0;
                    foreach ($permissions as $group) {
                        foreach ($group['permissions'] as $perm) {
                            $total_permissions++;
                            if ($perm['can_view'] || $perm['can_add'] || $perm['can_edit'] || $perm['can_delete']) {
                                $active_permissions++;
                            }
                        }
                    }
                    
                    $response = array(
                        'status' => 1,
                        'message' => 'Permissions retrieved successfully.',
                        'data' => array(
                            'role' => array(
                                'id' => $role ? $role->id : null,
                                'name' => $role ? $role->name : 'Unknown',
                                'slug' => $role ? $role->slug : null,
                                'is_superadmin' => $role ? (bool)$role->is_superadmin : false
                            ),
                            'permissions' => $permissions,
                            'summary' => array(
                                'total_permission_groups' => count($permissions),
                                'total_permissions' => $total_permissions,
                                'active_permissions' => $active_permissions
                            )
                        )
                    );
                    
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Accessible Modules
     * GET /teacher/modules
     */
    public function modules()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    
                    $modules = $this->teacher_permission_model->getTeacherModules($staff_id);
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    
                    $response = array(
                        'status' => 1,
                        'message' => 'Accessible modules retrieved successfully.',
                        'data' => array(
                            'role' => array(
                                'id' => $role ? $role->id : null,
                                'name' => $role ? $role->name : 'Unknown',
                                'slug' => $role ? $role->slug : null,
                                'is_superadmin' => $role ? (bool)$role->is_superadmin : false
                            ),
                            'modules' => $modules,
                            'total_modules' => count($modules)
                        )
                    );
                    
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Check Specific Permission
     * POST /teacher/check-permission
     */
    public function check_permission()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    $params = json_decode(file_get_contents('php://input'), true);
                    
                    // Validate required parameters
                    if (!isset($params['category']) || !isset($params['permission'])) {
                        json_output(400, array(
                            'status' => 400, 
                            'message' => 'Category and permission parameters are required.'
                        ));
                        return;
                    }
                    
                    $category = $params['category'];
                    $permission = $params['permission'];
                    
                    $has_permission = $this->teacher_permission_model->hasPrivilege($staff_id, $category, $permission);
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    
                    $response = array(
                        'status' => 1,
                        'message' => 'Permission check completed.',
                        'data' => array(
                            'category' => $category,
                            'permission' => $permission,
                            'has_permission' => $has_permission,
                            'role' => array(
                                'id' => $role ? $role->id : null,
                                'name' => $role ? $role->name : 'Unknown',
                                'is_superadmin' => $role ? (bool)$role->is_superadmin : false
                            )
                        )
                    );
                    
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Role Information
     * GET /teacher/role
     */
    public function role()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    $staff_info = $this->staff_model->get($staff_id);
                    
                    if ($role) {
                        $response = array(
                            'status' => 1,
                            'message' => 'Role information retrieved successfully.',
                            'data' => array(
                                'role' => array(
                                    'id' => $role->id,
                                    'name' => $role->name,
                                    'slug' => $role->slug,
                                    'is_superadmin' => (bool)$role->is_superadmin
                                ),
                                'staff_info' => array(
                                    'id' => $staff_info['id'],
                                    'employee_id' => $staff_info['employee_id'],
                                    'name' => $staff_info['name'] . ' ' . $staff_info['surname'],
                                    'designation' => $staff_info['designation'],
                                    'department' => $staff_info['department_name']
                                )
                            )
                        );
                    } else {
                        $response = array(
                            'status' => 0,
                            'message' => 'No role assigned to this teacher.',
                            'data' => array(
                                'role' => null,
                                'staff_info' => array(
                                    'id' => $staff_info['id'],
                                    'employee_id' => $staff_info['employee_id'],
                                    'name' => $staff_info['name'] . ' ' . $staff_info['surname'],
                                    'designation' => $staff_info['designation'],
                                    'department' => $staff_info['department_name']
                                )
                            )
                        );
                    }
                    
                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get System Settings for Teacher
     * GET /teacher/settings
     */
    public function settings()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $setting = $this->setting_model->get();

                    // Filter settings relevant to teachers
                    $teacher_settings = array(
                        'school_name' => $setting[0]['name'],
                        'school_code' => $setting[0]['dise_code'],
                        'session_id' => $setting[0]['session_id'],
                        'currency_symbol' => $setting[0]['currency_symbol'],
                        'currency' => $setting[0]['currency'],
                        'date_format' => $setting[0]['date_format'],
                        'time_format' => $setting[0]['time_format'],
                        'timezone' => $setting[0]['timezone'],
                        'language' => $setting[0]['language'],
                        'is_rtl' => $setting[0]['is_rtl'],
                        'theme' => $setting[0]['theme'],
                        'start_week' => $setting[0]['start_week'],
                        'student_login' => $setting[0]['student_login'],
                        'parent_login' => $setting[0]['parent_login']
                    );

                    $response = array(
                        'status' => 1,
                        'message' => 'System settings retrieved successfully.',
                        'data' => $teacher_settings
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Sidebar Menu Structure
     * GET /teacher/sidebar-menu
     */
    public function sidebar_menu()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];

                    $menus = $this->teacher_permission_model->getTeacherMenus($staff_id);

                    // Format for sidebar display
                    $sidebar_structure = array();
                    foreach ($menus as $menu) {
                        $sidebar_item = array(
                            'id' => $menu['id'],
                            'title' => $menu['menu'],
                            'icon' => $menu['icon'],
                            'key' => $menu['lang_key'],
                            'level' => $menu['level'],
                            'has_submenu' => count($menu['submenus']) > 0,
                            'submenu_count' => count($menu['submenus']),
                            'children' => array()
                        );

                        foreach ($menu['submenus'] as $submenu) {
                            $sidebar_item['children'][] = array(
                                'id' => $submenu['id'],
                                'title' => $submenu['menu'],
                                'key' => $submenu['key'],
                                'url' => $submenu['url'],
                                'controller' => $submenu['activate_controller'],
                                'methods' => explode(',', $submenu['activate_methods'])
                            );
                        }

                        $sidebar_structure[] = $sidebar_item;
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Sidebar menu structure retrieved successfully.',
                        'data' => array(
                            'sidebar_menu' => $sidebar_structure,
                            'total_main_menus' => count($sidebar_structure),
                            'total_submenus' => array_sum(array_column($sidebar_structure, 'submenu_count'))
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Navigation Breadcrumb
     * POST /teacher/breadcrumb
     */
    public function breadcrumb()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    $params = json_decode(file_get_contents('php://input'), true);

                    if (!isset($params['controller']) || !isset($params['method'])) {
                        json_output(400, array(
                            'status' => 400,
                            'message' => 'Controller and method parameters are required.'
                        ));
                        return;
                    }

                    $controller = $params['controller'];
                    $method_name = $params['method'];

                    // Find the menu item that matches the controller and method
                    $menus = $this->teacher_permission_model->getTeacherMenus($staff_id);
                    $breadcrumb = array();

                    foreach ($menus as $menu) {
                        foreach ($menu['submenus'] as $submenu) {
                            if ($submenu['activate_controller'] == $controller) {
                                $methods = explode(',', $submenu['activate_methods']);
                                if (in_array($method_name, $methods)) {
                                    $breadcrumb = array(
                                        'main_menu' => array(
                                            'id' => $menu['id'],
                                            'title' => $menu['menu'],
                                            'icon' => $menu['icon']
                                        ),
                                        'submenu' => array(
                                            'id' => $submenu['id'],
                                            'title' => $submenu['menu'],
                                            'url' => $submenu['url']
                                        ),
                                        'current' => array(
                                            'controller' => $controller,
                                            'method' => $method_name
                                        )
                                    );
                                    break 2;
                                }
                            }
                        }
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Breadcrumb information retrieved.',
                        'data' => array(
                            'breadcrumb' => $breadcrumb,
                            'found' => !empty($breadcrumb)
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Permission Groups
     * GET /teacher/permission-groups
     */
    public function permission_groups()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];

                    $permissions = $this->teacher_permission_model->getTeacherPermissions($staff_id);

                    $permission_groups = array();
                    foreach ($permissions as $group_code => $group_data) {
                        $active_permissions = 0;
                        $total_permissions = count($group_data['permissions']);

                        foreach ($group_data['permissions'] as $perm) {
                            if ($perm['can_view'] || $perm['can_add'] || $perm['can_edit'] || $perm['can_delete']) {
                                $active_permissions++;
                            }
                        }

                        $permission_groups[] = array(
                            'group_id' => $group_data['group_id'],
                            'group_name' => $group_data['group_name'],
                            'group_code' => $group_code,
                            'total_permissions' => $total_permissions,
                            'active_permissions' => $active_permissions,
                            'access_level' => $active_permissions > 0 ? 'granted' : 'denied'
                        );
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Permission groups retrieved successfully.',
                        'data' => array(
                            'permission_groups' => $permission_groups,
                            'total_groups' => count($permission_groups)
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Detailed Permissions for a Group
     * POST /teacher/group-permissions
     */
    public function group_permissions()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    $params = json_decode(file_get_contents('php://input'), true);

                    if (!isset($params['group_code'])) {
                        json_output(400, array(
                            'status' => 400,
                            'message' => 'Group code parameter is required.'
                        ));
                        return;
                    }

                    $group_code = $params['group_code'];
                    $permissions = $this->teacher_permission_model->getTeacherPermissions($staff_id);

                    if (isset($permissions[$group_code])) {
                        $group_data = $permissions[$group_code];

                        $detailed_permissions = array();
                        foreach ($group_data['permissions'] as $perm_code => $perm_data) {
                            $detailed_permissions[] = array(
                                'permission_id' => $perm_data['permission_id'],
                                'permission_name' => $perm_data['permission_name'],
                                'permission_code' => $perm_code,
                                'can_view' => $perm_data['can_view'],
                                'can_add' => $perm_data['can_add'],
                                'can_edit' => $perm_data['can_edit'],
                                'can_delete' => $perm_data['can_delete'],
                                'has_any_access' => $perm_data['can_view'] || $perm_data['can_add'] ||
                                                   $perm_data['can_edit'] || $perm_data['can_delete']
                            );
                        }

                        $response = array(
                            'status' => 1,
                            'message' => 'Group permissions retrieved successfully.',
                            'data' => array(
                                'group_info' => array(
                                    'group_id' => $group_data['group_id'],
                                    'group_name' => $group_data['group_name'],
                                    'group_code' => $group_code
                                ),
                                'permissions' => $detailed_permissions,
                                'total_permissions' => count($detailed_permissions)
                            )
                        );
                    } else {
                        $response = array(
                            'status' => 0,
                            'message' => 'Permission group not found or access denied.',
                            'data' => array(
                                'group_code' => $group_code,
                                'permissions' => array()
                            )
                        );
                    }

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Bulk Permission Check
     * POST /teacher/bulk-permission-check
     */
    public function bulk_permission_check()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    $params = json_decode(file_get_contents('php://input'), true);

                    if (!isset($params['permissions']) || !is_array($params['permissions'])) {
                        json_output(400, array(
                            'status' => 400,
                            'message' => 'Permissions array is required.'
                        ));
                        return;
                    }

                    $permission_checks = array();
                    foreach ($params['permissions'] as $perm_check) {
                        if (isset($perm_check['category']) && isset($perm_check['permission'])) {
                            $has_permission = $this->teacher_permission_model->hasPrivilege(
                                $staff_id,
                                $perm_check['category'],
                                $perm_check['permission']
                            );

                            $permission_checks[] = array(
                                'category' => $perm_check['category'],
                                'permission' => $perm_check['permission'],
                                'has_permission' => $has_permission,
                                'identifier' => isset($perm_check['identifier']) ? $perm_check['identifier'] : null
                            );
                        }
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Bulk permission check completed.',
                        'data' => array(
                            'permission_checks' => $permission_checks,
                            'total_checks' => count($permission_checks),
                            'granted_count' => count(array_filter($permission_checks, function($check) {
                                return $check['has_permission'];
                            }))
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Module Status
     * POST /teacher/module-status
     */
    public function module_status()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];
                    $params = json_decode(file_get_contents('php://input'), true);

                    if (!isset($params['module_code'])) {
                        json_output(400, array(
                            'status' => 400,
                            'message' => 'Module code parameter is required.'
                        ));
                        return;
                    }

                    $module_code = $params['module_code'];
                    $modules = $this->teacher_permission_model->getTeacherModules($staff_id);

                    $module_found = false;
                    $module_info = null;

                    foreach ($modules as $module) {
                        if ($module['group_code'] == $module_code) {
                            $module_found = true;
                            $module_info = $module;
                            break;
                        }
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Module status retrieved successfully.',
                        'data' => array(
                            'module_code' => $module_code,
                            'is_accessible' => $module_found,
                            'module_info' => $module_info
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Features Access
     * GET /teacher/features
     */
    public function features()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];

                    // Define common teacher features and check access
                    $features = array(
                        'student_management' => array(
                            'name' => 'Student Management',
                            'permissions' => array(
                                array('category' => 'student_information', 'permission' => 'view'),
                                array('category' => 'student_information', 'permission' => 'edit')
                            )
                        ),
                        'attendance' => array(
                            'name' => 'Attendance Management',
                            'permissions' => array(
                                array('category' => 'attendance', 'permission' => 'view'),
                                array('category' => 'attendance', 'permission' => 'add')
                            )
                        ),
                        'examinations' => array(
                            'name' => 'Examinations',
                            'permissions' => array(
                                array('category' => 'examinations', 'permission' => 'view'),
                                array('category' => 'examinations', 'permission' => 'add')
                            )
                        ),
                        'homework' => array(
                            'name' => 'Homework Management',
                            'permissions' => array(
                                array('category' => 'homework', 'permission' => 'view'),
                                array('category' => 'homework', 'permission' => 'add')
                            )
                        ),
                        'lesson_plan' => array(
                            'name' => 'Lesson Planning',
                            'permissions' => array(
                                array('category' => 'lesson_plan', 'permission' => 'view'),
                                array('category' => 'lesson_plan', 'permission' => 'add')
                            )
                        ),
                        'communicate' => array(
                            'name' => 'Communication',
                            'permissions' => array(
                                array('category' => 'communicate', 'permission' => 'view'),
                                array('category' => 'communicate', 'permission' => 'add')
                            )
                        ),
                        'reports' => array(
                            'name' => 'Reports',
                            'permissions' => array(
                                array('category' => 'reports', 'permission' => 'view')
                            )
                        )
                    );

                    $feature_access = array();
                    foreach ($features as $feature_code => $feature_data) {
                        $has_access = false;
                        $granted_permissions = array();

                        foreach ($feature_data['permissions'] as $perm) {
                            $has_perm = $this->teacher_permission_model->hasPrivilege(
                                $staff_id,
                                $perm['category'],
                                $perm['permission']
                            );

                            if ($has_perm) {
                                $has_access = true;
                                $granted_permissions[] = $perm['permission'];
                            }
                        }

                        $feature_access[] = array(
                            'feature_code' => $feature_code,
                            'feature_name' => $feature_data['name'],
                            'has_access' => $has_access,
                            'granted_permissions' => $granted_permissions,
                            'total_permissions' => count($feature_data['permissions'])
                        );
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Teacher features access retrieved successfully.',
                        'data' => array(
                            'features' => $feature_access,
                            'total_features' => count($feature_access),
                            'accessible_features' => count(array_filter($feature_access, function($f) {
                                return $f['has_access'];
                            }))
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Get Teacher Dashboard Summary
     * GET /teacher/dashboard-summary
     */
    public function dashboard_summary()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $auth_check = $this->teacher_auth_model->auth();
                if ($auth_check['status'] == 200) {
                    $staff_id = $auth_check['staff_id'];

                    // Get comprehensive summary
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    $permissions = $this->teacher_permission_model->getTeacherPermissions($staff_id);
                    $modules = $this->teacher_permission_model->getTeacherModules($staff_id);
                    $menus = $this->teacher_permission_model->getTeacherMenus($staff_id);

                    // Calculate statistics
                    $total_permissions = 0;
                    $active_permissions = 0;
                    foreach ($permissions as $group) {
                        foreach ($group['permissions'] as $perm) {
                            $total_permissions++;
                            if ($perm['can_view'] || $perm['can_add'] || $perm['can_edit'] || $perm['can_delete']) {
                                $active_permissions++;
                            }
                        }
                    }

                    $total_submenus = 0;
                    foreach ($menus as $menu) {
                        $total_submenus += count($menu['submenus']);
                    }

                    $response = array(
                        'status' => 1,
                        'message' => 'Dashboard summary retrieved successfully.',
                        'data' => array(
                            'role_info' => array(
                                'id' => $role ? $role->id : null,
                                'name' => $role ? $role->name : 'Unknown',
                                'is_superadmin' => $role ? (bool)$role->is_superadmin : false
                            ),
                            'access_summary' => array(
                                'total_permission_groups' => count($permissions),
                                'total_permissions' => $total_permissions,
                                'active_permissions' => $active_permissions,
                                'permission_percentage' => $total_permissions > 0 ?
                                    round(($active_permissions / $total_permissions) * 100, 2) : 0,
                                'accessible_modules' => count($modules),
                                'main_menus' => count($menus),
                                'submenus' => $total_submenus
                            ),
                            'quick_stats' => array(
                                'has_student_access' => $this->teacher_permission_model->hasPrivilege($staff_id, 'student_information', 'view'),
                                'has_attendance_access' => $this->teacher_permission_model->hasPrivilege($staff_id, 'attendance', 'view'),
                                'has_exam_access' => $this->teacher_permission_model->hasPrivilege($staff_id, 'examinations', 'view'),
                                'has_homework_access' => $this->teacher_permission_model->hasPrivilege($staff_id, 'homework', 'view'),
                                'has_report_access' => $this->teacher_permission_model->hasPrivilege($staff_id, 'reports', 'view')
                            )
                        )
                    );

                    json_output(200, $response);
                } else {
                    json_output(401, $auth_check);
                }
            }
        }
    }

    /**
     * Simple test method - no dependencies
     */
    public function test()
    {
        try {
            $response = array(
                'status' => 1,
                'message' => 'Teacher webservice test successful',
                'timestamp' => date('Y-m-d H:i:s'),
                'controller' => 'Teacher_webservice',
                'method' => 'test',
                'environment' => ENVIRONMENT,
                'php_version' => PHP_VERSION,
                'codeigniter_version' => CI_VERSION
            );
            
            json_output(200, $response);
        } catch (Exception $e) {
            $error_response = array(
                'status' => 0,
                'message' => 'Test method failed',
                'error' => array(
                    'type' => get_class($e),
                    'message' => $e->getMessage()
                ),
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            json_output(500, $error_response);
        }
    }

    /**
     * Simple menu test - minimal dependencies using actual database structure
     */
    public function simple_menu()
    {
        try {
            $method = $this->input->server('REQUEST_METHOD');

            if ($method != 'POST') {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'Bad request. Only POST method allowed.',
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Get JSON input
            $json_input = json_decode($this->input->raw_input_stream, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'Invalid JSON format in request body',
                    'error' => json_last_error_msg(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }
            
            if (empty($json_input) || !isset($json_input['staff_id'])) {
                json_output(400, array(
                    'status' => 0, 
                    'message' => 'staff_id is required in request body',
                    'example' => array('staff_id' => 123),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            $staff_id = intval($json_input['staff_id']);

            if ($staff_id <= 0) {
                json_output(400, array(
                    'status' => 0,
                    'message' => 'staff_id must be a valid positive integer',
                    'provided' => $json_input['staff_id'],
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Check database connection
            if (!$this->db->conn_id) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Database connection failed',
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Get staff info directly from database
            $this->db->select('s.*, r.name as role_name, r.is_superadmin, r.id as role_id');
            $this->db->from('staff s');
            $this->db->join('staff_roles sr', 'sr.staff_id = s.id', 'left');
            $this->db->join('roles r', 'r.id = sr.role_id', 'left');
            $this->db->where('s.id', $staff_id);
            $this->db->where('s.is_active', 1);
            
            $query = $this->db->get();
            
            if (!$query) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Database query failed',
                    'error' => $this->db->error(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }
            
            $staff_info = $query->row();

            if (!$staff_info) {
                json_output(404, array(
                    'status' => 0,
                    'message' => 'Staff member not found or inactive',
                    'staff_id' => $staff_id,
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            // Check if superadmin
            $is_superadmin = ($staff_info->role_id == 7 || $staff_info->is_superadmin == 1);

            // Get ALL menus (we'll filter by access_permissions)
            $this->db->select('*');
            $this->db->from('sidebar_menus');
            $this->db->where('is_active', 1);
            $this->db->where('sidebar_display', 1);
            $this->db->order_by('level');
            $menu_query = $this->db->get();

            if (!$menu_query) {
                json_output(500, array(
                    'status' => 0,
                    'message' => 'Failed to fetch menus',
                    'error' => $this->db->error(),
                    'timestamp' => date('Y-m-d H:i:s')
                ));
                return;
            }

            $all_menus = $menu_query->result_array();

            // Get ALL submenus
            $this->db->select('*');
            $this->db->from('sidebar_sub_menus');
            $this->db->where('is_active', 1);
            $this->db->order_by('sidebar_menu_id, level');
            $submenu_query = $this->db->get();
            $all_submenus = $submenu_query ? $submenu_query->result_array() : array();

            // Group submenus by menu_id
            $submenus_by_menu = array();
            foreach ($all_submenus as $submenu) {
                $submenus_by_menu[$submenu['sidebar_menu_id']][] = $submenu;
            }

            // Filter menus and submenus using access_permissions (like admin dashboard)
            $menus = array();
            foreach ($all_menus as $menu) {
                // Check menu permission using access_permissions field
                $module_permission = $this->access_permission_sidebar_remove_pipe($menu['access_permissions']);
                $module_access = false;

                if ($is_superadmin) {
                    $module_access = true;
                } elseif (!empty($module_permission)) {
                    foreach ($module_permission as $m_permission_value) {
                        $cat_permission = $this->access_permission_remove_comma($m_permission_value);

                        if (count($cat_permission) >= 2) {
                            if ($this->hasPrivilege($staff_info->role_id, $staff_info->role_name, $cat_permission[0], $cat_permission[1])) {
                                $module_access = true;
                                break;
                            }
                        }
                    }
                }

                if ($module_access) {
                    // Filter submenus for this menu
                    $menu['submenus'] = array();

                    if (isset($submenus_by_menu[$menu['id']])) {
                        foreach ($submenus_by_menu[$menu['id']] as $submenu) {
                            $sidebar_permission = $this->access_permission_sidebar_remove_pipe($submenu['access_permissions']);
                            $sidebar_access = false;

                            if ($is_superadmin) {
                                $sidebar_access = true;
                            } elseif (!empty($sidebar_permission)) {
                                foreach ($sidebar_permission as $sidebar_permission_value) {
                                    $sidebar_cat_permission = $this->access_permission_remove_comma($sidebar_permission_value);

                                    if (count($sidebar_cat_permission) >= 2) {
                                        if ($this->hasPrivilege($staff_info->role_id, $staff_info->role_name, $sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                            $sidebar_access = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($sidebar_access) {
                                $menu['submenus'][] = $submenu;
                            }
                        }
                    }

                    $menus[] = $menu;
                }
            }

            $response = array(
                'status' => 1,
                'message' => 'Menu items retrieved successfully',
                'data' => array(
                    'staff_id' => $staff_id,
                    'staff_info' => array(
                        'id' => (int)$staff_info->id,
                        'name' => $staff_info->name,
                        'surname' => $staff_info->surname,
                        'employee_id' => $staff_info->employee_id,
                        'full_name' => trim($staff_info->name . ' ' . $staff_info->surname)
                    ),
                    'role' => array(
                        'id' => $staff_info->role_id ? (int)$staff_info->role_id : null,
                        'name' => $staff_info->role_name ? $staff_info->role_name : 'No Role Assigned',
                        'is_superadmin' => $is_superadmin
                    ),
                    'menus' => $menus,
                    'total_menus' => count($menus),
                    'timestamp' => date('Y-m-d H:i:s')
                )
            );
            
            json_output(200, $response);
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 0,
                'message' => 'Exception occurred while retrieving menu items',
                'error' => array(
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ),
                'staff_id' => isset($staff_id) ? $staff_id : null,
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            log_message('error', 'Simple Menu Exception: ' . $e->getMessage());
            json_output(500, $error_response);
        }
    }

    /**
     * Simple test method
     */
    public function debug_test()
    {
        json_output(200, array(
            'status' => 1,
            'message' => 'Teacher webservice debug test successful',
            'timestamp' => date('Y-m-d H:i:s'),
            'controller' => 'Teacher_webservice'
        ));
    }

    /**
     * Get Staff Attendance Summary
     * POST /teacher/attendance-summary
     *
     * Comprehensive attendance API that returns detailed attendance statistics
     * for staff members including dates, leave information, and summaries.
     */
    public function attendance_summary()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Don't display errors in output

        // Log that method was called
        log_message('info', 'attendance_summary method called');

        try {
            $method = $this->input->server('REQUEST_METHOD');

            if ($method != 'POST') {
                json_output(400, array('status' => 400, 'message' => 'Bad request. Only POST method allowed.'));
                return;
            }

            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if (!$check_auth_client) {
                json_output(401, array('status' => 401, 'message' => 'Unauthorized. Please check Client-Service and Auth-Key headers.'));
                return;
            }

            // For attendance summary, we only require client authentication
            // This allows administrative access to attendance data without user-specific tokens

            // Load required models
            $this->load->model('staffattendancemodel');
            $this->load->model('leaverequest_model');

            // Get request parameters
            $params = json_decode(file_get_contents('php://input'), true);

            // Validate JSON input
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'Invalid JSON format in request body.'
                ));
                return;
            }

            // Extract parameters with defaults
            $staff_id = isset($params['staff_id']) ? (int)$params['staff_id'] : null;
            $from_date = isset($params['from_date']) ? trim($params['from_date']) : null;
            $to_date = isset($params['to_date']) ? trim($params['to_date']) : null;

            // Validate staff_id if provided
            if ($staff_id !== null && $staff_id <= 0) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'Invalid staff_id. Must be a positive integer.'
                ));
                return;
            }

            // Validate date formats if provided
            if ($from_date && !$this->isValidDate($from_date)) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'Invalid from_date format. Use YYYY-MM-DD format.'
                ));
                return;
            }

            if ($to_date && !$this->isValidDate($to_date)) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'Invalid to_date format. Use YYYY-MM-DD format.'
                ));
                return;
            }

            // Check date range validity
            if ($from_date && $to_date && strtotime($from_date) > strtotime($to_date)) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'from_date cannot be greater than to_date.'
                ));
                return;
            }

            // Get attendance summary data
            $attendance_data = $this->staffattendancemodel->getAttendanceSummary($staff_id, $from_date, $to_date);

            // Check for errors in the model response
            if (isset($attendance_data['error'])) {
                json_output(400, array(
                    'status' => 400,
                    'message' => $attendance_data['error']
                ));
                return;
            }

            // Prepare successful response
            $response = array(
                'status' => 1,
                'message' => 'Attendance summary retrieved successfully.',
                'data' => $attendance_data,
                'request_info' => array(
                    'staff_id' => $staff_id,
                    'from_date' => $from_date ?: date('Y-01-01'),
                    'to_date' => $to_date ?: date('Y-12-31'),
                    'generated_at' => date('Y-m-d H:i:s')
                )
            );

            json_output(200, $response);

        } catch (Exception $e) {
            // Log the error for debugging
            log_message('error', 'Staff Attendance Summary API Error: ' . $e->getMessage());

            json_output(500, array(
                'status' => 500,
                'message' => 'Internal server error: ' . $e->getMessage()
            ));
        } catch (Error $e) {
            // Log PHP errors
            log_message('error', 'Staff Attendance Summary PHP Error: ' . $e->getMessage());

            json_output(500, array(
                'status' => 500,
                'message' => 'PHP Error: ' . $e->getMessage()
            ));
        }
    }

    /**
     * Get Staff Attendance - Simplified endpoint that automatically finds all attendance data
     * POST /teacher/staff-attendance
     *
     * This endpoint automatically determines the date range based on available data
     * and returns all attendance records for the specified staff member.
     */
    public function staff_attendance()
    {
        try {
            $method = $this->input->server('REQUEST_METHOD');

            if ($method != 'POST') {
                json_output(400, array('status' => 400, 'message' => 'Bad request. Only POST method allowed.'));
                return;
            }

            $check_auth_client = $this->teacher_auth_model->check_auth_client();
            if (!$check_auth_client) {
                json_output(401, array('status' => 401, 'message' => 'Unauthorized. Please check Client-Service and Auth-Key headers.'));
                return;
            }

            // Load required models
            $this->load->model('staffattendancemodel');

            // Get request parameters
            $params = json_decode(file_get_contents('php://input'), true);

            // Validate JSON input
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'Invalid JSON format in request body.'
                ));
                return;
            }

            // Extract staff_id (required for this endpoint)
            $staff_id = isset($params['staff_id']) ? (int)$params['staff_id'] : null;

            if (empty($staff_id)) {
                json_output(400, array(
                    'status' => 400,
                    'message' => 'staff_id parameter is required.'
                ));
                return;
            }

            // Get attendance data without specifying dates (will auto-detect range)
            $attendance_data = $this->staffattendancemodel->getAttendanceSummary($staff_id);

            // Check for errors in the model response
            if (isset($attendance_data['error'])) {
                json_output(400, array(
                    'status' => 400,
                    'message' => $attendance_data['error']
                ));
                return;
            }

            // Prepare successful response
            $response = array(
                'status' => 1,
                'message' => 'Staff attendance retrieved successfully.',
                'data' => $attendance_data,
                'note' => 'This endpoint automatically detects the date range based on available attendance data.',
                'generated_at' => date('Y-m-d H:i:s')
            );

            json_output(200, $response);

        } catch (Exception $e) {
            log_message('error', 'Staff Attendance API Error: ' . $e->getMessage());

            json_output(500, array(
                'status' => 500,
                'message' => 'Internal server error: ' . $e->getMessage()
            ));
        } catch (Error $e) {
            log_message('error', 'Staff Attendance PHP Error: ' . $e->getMessage());

            json_output(500, array(
                'status' => 500,
                'message' => 'PHP Error: ' . $e->getMessage()
            ));
        }
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate($date)
    {
        if (empty($date)) {
            return false;
        }
        $d = date_create_from_format('Y-m-d', $date);
        return $d && date_format($d, 'Y-m-d') === $date;
    }

    /**
     * Debug Menu - Test menu retrieval without authentication
     * GET /teacher/debug-menu?staff_id=1
     */
    public function debug_menu()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $staff_id = $this->input->get('staff_id');
        if (empty($staff_id)) {
            json_output(400, array(
                'status' => 400,
                'message' => 'staff_id parameter is required.'
            ));
            return;
        }

        try {
            // Load the teacher permission model
            $this->load->model('teacher_permission_model');
            
            // Get role information
            $role = $this->teacher_permission_model->getTeacherRole($staff_id);
            
            // Get menus
            $menus = $this->teacher_permission_model->getTeacherMenus($staff_id);
            
            // Get permissions
            $permissions = $this->teacher_permission_model->getTeacherPermissions($staff_id);
            
            // Get staff info from database
            $this->db->select('s.*, r.name as role_name, r.is_superadmin');
            $this->db->from('staff s');
            $this->db->join('staff_roles sr', 'sr.staff_id = s.id', 'left');
            $this->db->join('roles r', 'r.id = sr.role_id', 'left');
            $this->db->where('s.id', $staff_id);
            $staff_info = $this->db->get()->row();
            
            $response = array(
                'status' => 1,
                'message' => 'Debug menu data retrieved successfully.',
                'data' => array(
                    'staff_id' => $staff_id,
                    'staff_info' => $staff_info,
                    'role' => $role,
                    'menus' => $menus,
                    'total_menus' => count($menus),
                    'permissions' => $permissions,
                    'debug_info' => array(
                        'timestamp' => date('Y-m-d H:i:s'),
                        'staff_exists' => !empty($staff_info),
                        'role_found' => !empty($role),
                        'menu_count' => count($menus),
                        'permission_groups' => count($permissions)
                    )
                )
            );
            
            json_output(200, $response);
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 0,
                'message' => 'Error in debug menu retrieval',
                'error' => $e->getMessage(),
                'debug_info' => array(
                    'staff_id' => $staff_id,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            json_output(500, $error_response);
        }
    }

    /**
     * Parse access_permissions field (replicate menu_helper.php logic)
     * Removes pipe signs and parentheses
     */
    private function access_permission_sidebar_remove_pipe($access_permissions)
    {
        if (empty($access_permissions)) {
            return array();
        }
        // remove pipe sign ||
        $module_permission = array_map('trim', explode('||', preg_replace('/\(\'|\'|\)/', '', $access_permissions)));
        return $module_permission;
    }

    /**
     * Parse comma-separated permission values
     */
    private function access_permission_remove_comma($m_permission_value)
    {
        if (empty($m_permission_value)) {
            return array();
        }
        // remove comma
        $module_permission_seprated = array_map('trim', explode(',', preg_replace('/\s+/', '', $m_permission_value)));
        return $module_permission_seprated;
    }

    /**
     * Check if staff has privilege (replicate RBAC logic)
     */
    private function hasPrivilege($role_id, $role_name, $category, $permission)
    {
        // Super Admin has all privileges
        if ($role_name == 'Super Admin') {
            return true;
        }

        // Check if rolepermission_model is loaded
        if (!isset($this->rolepermission_model)) {
            // Try to load it
            try {
                $this->load->model('rolepermission_model');
            } catch (Exception $e) {
                log_message('error', 'Failed to load rolepermission_model: ' . $e->getMessage());
                return false;
            }
        }

        // Verify model is loaded
        if (!isset($this->rolepermission_model) || !is_object($this->rolepermission_model)) {
            log_message('error', 'rolepermission_model is not available');
            return false;
        }

        try {
            // Get permission from database
            $role_perm = $this->rolepermission_model->getPermissionByRoleandCategory($role_id, trim($category));

            if ($role_perm && isset($role_perm[$permission])) {
                return ($role_perm[$permission] == 1);
            }
        } catch (Exception $e) {
            log_message('error', 'Error checking privilege: ' . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Handle 404 errors with JSON response
     */
    public function not_found()
    {
        $response = array(
            'status' => 0,
            'message' => 'API endpoint not found',
            'error' => array(
                'type' => 'Not Found',
                'code' => 404,
                'uri' => $this->uri->uri_string(),
                'method' => $this->input->server('REQUEST_METHOD')
            ),
            'available_endpoints' => array(
                'POST /teacher/simple_menu' => 'Get menu items for staff',
                'POST /teacher/menu' => 'Get menu items (original)',
                'GET /teacher/test' => 'Test endpoint',
                'GET /teacher/debug-menu' => 'Debug menu endpoint'
            ),
            'timestamp' => date('Y-m-d H:i:s')
        );

        json_output(404, $response);
    }
}
