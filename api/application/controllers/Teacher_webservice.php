<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_webservice extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'teacher_auth_model', 'teacher_permission_model', 'staff_model', 
            'setting_model', 'role_model', 'rolepermission_model'
        ));
        $this->load->library(array('teacher_middleware', 'customlib'));
        $this->load->helper('teacher_auth');

        $setting = $this->setting_model->getSchoolDetail();
        if ($setting->timezone != "") {
            date_default_timezone_set($setting->timezone);
        } else {
            date_default_timezone_set('UTC');
        }
    }

    /**
     * Get Teacher Menu Items
     * GET /teacher/menu
     */
    public function menu()
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
                    $role = $this->teacher_permission_model->getTeacherRole($staff_id);
                    
                    $response = array(
                        'status' => 1,
                        'message' => 'Menu items retrieved successfully.',
                        'data' => array(
                            'role' => array(
                                'id' => $role ? $role->id : null,
                                'name' => $role ? $role->name : 'Unknown',
                                'slug' => $role ? $role->slug : null,
                                'is_superadmin' => $role ? (bool)$role->is_superadmin : false
                            ),
                            'menus' => $menus,
                            'total_menus' => count($menus)
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
}
