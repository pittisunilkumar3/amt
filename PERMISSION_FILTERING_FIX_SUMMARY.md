# Permission-Based Menu Filtering Fix

## Issue Summary
The teacher menu API endpoint was returning ALL submenus regardless of staff member permissions, instead of filtering based on role-based access control.

## Problem Details

### API Endpoint
- **URL**: `POST http://localhost/amt/api/teacher/menu`
- **Payload**: `{staff_id: 1}`

### Original Behavior
- The API was correctly filtering parent menus based on role permissions
- However, submenus were NOT being filtered - all active submenus were returned
- This was a security issue as staff members could see menu items they shouldn't have access to

### Root Cause
In `api/application/controllers/Teacher_webservice.php`, the submenu retrieval logic (lines 284-300 and 1402-1418) was:
1. Only checking if submenus were active (`is_active = 1`)
2. Not joining with permission tables to verify role access
3. Comment even stated: "If user has access to parent menu, show ALL active submenus"

## Solution Implemented

### Changes Made
Modified two methods in `api/application/controllers/Teacher_webservice.php`:
1. **`menu()` method** (lines 284-314)
2. **`simple_menu()` method** (lines 1402-1432)

### New Logic
The fix implements proper permission-based filtering for submenus:

#### For Superadmin Users
- Returns all active submenus (no filtering needed)
- Maintains existing superadmin privileges

#### For Non-Superadmin Users
- Filters submenus using database joins:
  - `sidebar_sub_menus` → `permission_category` (via `permission_group_id`)
  - `permission_category` → `roles_permissions` (via `perm_cat_id`)
- Only returns submenus where:
  - Role ID matches staff member's role
  - `can_view` permission is set to 1
  - Submenu is active
  - Submenu belongs to the parent menu

### Database Schema Used
```sql
sidebar_sub_menus
├── permission_group_id → permission_category.perm_group_id
    └── permission_category.id → roles_permissions.perm_cat_id
        └── roles_permissions.role_id (matched with staff role)
            └── roles_permissions.can_view = 1
```

## Code Changes

### Before (Incorrect Implementation)
```php
foreach ($menus as &$menu) {
    $this->db->select('*');
    $this->db->from('sidebar_sub_menus');
    $this->db->where('sidebar_menu_id', $menu['id']);
    $this->db->where('is_active', 1);
    $this->db->order_by('level');
    $submenu_query = $this->db->get();
    
    if ($submenu_query) {
        $menu['submenus'] = $submenu_query->result_array();
    } else {
        $menu['submenus'] = array();
    }
}
```

### After (Correct Implementation)
```php
foreach ($menus as &$menu) {
    if ($is_superadmin) {
        // Superadmin gets all active submenus
        $this->db->select('*');
        $this->db->from('sidebar_sub_menus');
        $this->db->where('sidebar_menu_id', $menu['id']);
        $this->db->where('is_active', 1);
        $this->db->order_by('level');
        $submenu_query = $this->db->get();
    } else {
        // Non-superadmin: filter submenus based on role permissions
        $this->db->select('ssm.*');
        $this->db->distinct();
        $this->db->from('sidebar_sub_menus ssm');
        $this->db->join('permission_category pc', 'ssm.permission_group_id = pc.perm_group_id');
        $this->db->join('roles_permissions rp', 'pc.id = rp.perm_cat_id');
        $this->db->where('rp.role_id', $staff_info->role_id);
        $this->db->where('rp.can_view', 1);
        $this->db->where('ssm.sidebar_menu_id', $menu['id']);
        $this->db->where('ssm.is_active', 1);
        $this->db->order_by('ssm.level');
        $submenu_query = $this->db->get();
    }
    
    if ($submenu_query) {
        $menu['submenus'] = $submenu_query->result_array();
    } else {
        $menu['submenus'] = array();
    }
}
```

## Reference Implementation
The fix was based on the correct implementation found in `standalone_menu_api.php` (lines 119-127), which already had proper permission filtering for submenus.

## Testing Recommendations

### Test Case 1: Superadmin User
**Input**: `{staff_id: <superadmin_id>}`
**Expected**: All active menus and submenus returned

### Test Case 2: Regular Staff with Limited Permissions
**Input**: `{staff_id: <regular_staff_id>}`
**Expected**: Only menus and submenus the staff has `can_view` permission for

### Test Case 3: Staff with No Permissions
**Input**: `{staff_id: <staff_with_no_permissions>}`
**Expected**: Empty or minimal menu list

### Test Case 4: Compare with Admin Dashboard
**Action**: Compare API response with what the same staff member sees in the admin dashboard sidebar
**Expected**: API response should match dashboard sidebar menu items

## Security Impact
- **Before**: Potential information disclosure - staff could see menu items they shouldn't access
- **After**: Proper role-based access control enforced at API level

## Backward Compatibility
- API response structure remains unchanged
- Only the filtering logic is enhanced
- Superadmin behavior unchanged
- No breaking changes to API consumers

## Files Modified
1. `api/application/controllers/Teacher_webservice.php`
   - Line 284-314: `menu()` method submenu filtering
   - Line 1402-1432: `simple_menu()` method submenu filtering

## Related Files (Reference Only)
- `standalone_menu_api.php`: Reference implementation with correct filtering
- `application/views/layout/sidebar.php`: Admin dashboard sidebar implementation
- `api/application/models/Teacher_permission_model.php`: Permission checking model

## Database Tables Involved
- `sidebar_menus`: Parent menu items
- `sidebar_sub_menus`: Child menu items
- `permission_category`: Permission categories
- `permission_group`: Permission groups
- `roles_permissions`: Role-permission mappings
- `staff`: Staff information
- `staff_roles`: Staff role assignments
- `roles`: Role definitions

## Next Steps
1. Test the API endpoint with different staff IDs and roles
2. Verify the response matches expected permissions
3. Compare with admin dashboard sidebar to ensure consistency
4. Monitor for any edge cases or issues
5. Consider adding automated tests for permission filtering

## Notes
- The fix ensures consistency between the API and the admin dashboard
- Both parent menus and submenus now properly respect role permissions
- The implementation follows the existing permission model in the application

