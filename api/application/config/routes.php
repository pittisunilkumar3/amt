<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes in the
  | controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Test Routes
$route['test-db'] = 'test_db/index';
$route['test-db/staff'] = 'test_db/test_staff';
$route['test-db/settings'] = 'test_db/test_settings';
$route['test-db/auth-tables'] = 'test_db/test_auth_tables';

// Teacher Authentication Routes
$route['teacher/test'] = 'teacher_auth/test';
$route['teacher/login'] = 'teacher_auth/login';
$route['teacher/logout'] = 'teacher_auth/logout';
$route['teacher/profile'] = 'teacher_auth/profile';
$route['teacher/profile/update'] = 'teacher_auth/update_profile';
$route['teacher/change-password'] = 'teacher_auth/change_password';
$route['teacher/dashboard'] = 'teacher_auth/dashboard';
$route['teacher/refresh-token'] = 'teacher_auth/refresh_token';
$route['teacher/validate-token'] = 'teacher_auth/validate_token';

// Teacher Webservice Routes
$route['teacher/menu'] = 'teacher_webservice/menu';
$route['teacher/permissions'] = 'teacher_webservice/permissions';
$route['teacher/modules'] = 'teacher_webservice/modules';
$route['teacher/check-permission'] = 'teacher_webservice/check_permission';
$route['teacher/role'] = 'teacher_webservice/role';
$route['teacher/settings'] = 'teacher_webservice/settings';
$route['teacher/sidebar-menu'] = 'teacher_webservice/sidebar_menu';
$route['teacher/breadcrumb'] = 'teacher_webservice/breadcrumb';
$route['teacher/permission-groups'] = 'teacher_webservice/permission_groups';
$route['teacher/group-permissions'] = 'teacher_webservice/group_permissions';
$route['teacher/bulk-permission-check'] = 'teacher_webservice/bulk_permission_check';
$route['teacher/module-status'] = 'teacher_webservice/module_status';
$route['teacher/features'] = 'teacher_webservice/features';
$route['teacher/dashboard-summary'] = 'teacher_webservice/dashboard_summary';
