# Testing Guide for Menu Permission Filtering Fix

## Overview
This guide provides step-by-step instructions to test the permission-based menu filtering fix for the teacher menu API endpoint.

## Prerequisites
- Web server running (Apache/XAMPP)
- Database accessible (school6)
- API endpoint accessible at `http://localhost/amt/api/teacher/menu`

## Testing Tools Provided

### 1. Interactive Test Page
**File**: `test_menu_permissions.php`
**URL**: `http://localhost/amt/test_menu_permissions.php`

**Features**:
- Visual interface to test different staff members
- Displays staff list with roles
- Shows menu and submenu results
- Provides raw JSON response
- Copy JSON to clipboard functionality

**How to Use**:
1. Open the URL in your browser
2. Click on any staff member card
3. Review the returned menus and submenus
4. Verify that only permitted items are shown

### 2. Helper Script
**File**: `get_staff_list.php`
**Purpose**: Provides staff list data for the test page
**Note**: This is called automatically by the test page

## Manual Testing with cURL

### Test Case 1: Superadmin User
```bash
curl -X POST http://localhost/amt/api/teacher/menu \
  -H "Content-Type: application/json" \
  -d '{"staff_id": 1}'
```

**Expected Result**:
- All active menus returned
- All active submenus for each menu returned
- `is_superadmin: true` in response

### Test Case 2: Regular Staff with Limited Permissions
```bash
curl -X POST http://localhost/amt/api/teacher/menu \
  -H "Content-Type: application/json" \
  -d '{"staff_id": 6}'
```

**Expected Result**:
- Only menus the staff has permission to view
- Only submenus the staff has permission to view
- `is_superadmin: false` in response

### Test Case 3: Invalid Staff ID
```bash
curl -X POST http://localhost/amt/api/teacher/menu \
  -H "Content-Type: application/json" \
  -d '{"staff_id": 99999}'
```

**Expected Result**:
- HTTP 404 status
- Error message: "Staff member not found or inactive"

## Manual Testing with Postman

### Setup
1. Open Postman
2. Create a new POST request
3. URL: `http://localhost/amt/api/teacher/menu`
4. Headers: `Content-Type: application/json`
5. Body (raw JSON):
```json
{
  "staff_id": 1
}
```

### Test Scenarios

#### Scenario 1: Verify Superadmin Access
**Staff ID**: 1 (or any superadmin)
**Expected**: All menus and submenus

#### Scenario 2: Verify Role-Based Filtering
**Staff ID**: 6 (or any non-superadmin)
**Expected**: Filtered menus and submenus based on role

#### Scenario 3: Compare Different Roles
Test multiple staff IDs with different roles:
- Accountant
- Teacher
- Librarian
- Receptionist

**Expected**: Each role should see different menu items

## Verification Checklist

### ✅ Response Structure
- [ ] Response has `status: 1` for success
- [ ] Response includes `staff_info` object
- [ ] Response includes `role` object with `is_superadmin` flag
- [ ] Response includes `menus` array
- [ ] Each menu has `submenus` array

### ✅ Permission Filtering
- [ ] Superadmin sees all menus
- [ ] Superadmin sees all submenus
- [ ] Non-superadmin sees only permitted menus
- [ ] Non-superadmin sees only permitted submenus
- [ ] Staff with no permissions sees empty or minimal menu list

### ✅ Data Integrity
- [ ] Menu IDs are correct
- [ ] Submenu IDs are correct
- [ ] Menu names are displayed correctly
- [ ] Submenu URLs are correct
- [ ] Menu levels are in correct order

### ✅ Error Handling
- [ ] Invalid staff_id returns 404
- [ ] Missing staff_id returns 400
- [ ] Invalid JSON returns 400
- [ ] Database errors return 500

## Database Verification Queries

### Check Staff Role and Permissions
```sql
SELECT 
    s.id,
    s.name,
    s.surname,
    s.employee_id,
    r.id as role_id,
    r.name as role_name,
    s.is_superadmin
FROM staff s
LEFT JOIN staff_roles sr ON sr.staff_id = s.id
LEFT JOIN roles r ON r.id = sr.role_id
WHERE s.is_active = 1
ORDER BY s.id;
```

### Check Menu Permissions for a Role
```sql
SELECT DISTINCT
    sm.id,
    sm.menu,
    sm.permission_group_id,
    pc.name as permission_category,
    rp.can_view
FROM sidebar_menus sm
JOIN permission_category pc ON sm.permission_group_id = pc.perm_group_id
JOIN roles_permissions rp ON pc.id = rp.perm_cat_id
WHERE rp.role_id = 3  -- Replace with actual role_id
  AND rp.can_view = 1
  AND sm.is_active = 1
ORDER BY sm.level;
```

### Check Submenu Permissions for a Role
```sql
SELECT DISTINCT
    ssm.id,
    ssm.menu,
    ssm.sidebar_menu_id,
    ssm.permission_group_id,
    pc.name as permission_category,
    rp.can_view
FROM sidebar_sub_menus ssm
JOIN permission_category pc ON ssm.permission_group_id = pc.perm_group_id
JOIN roles_permissions rp ON pc.id = rp.perm_cat_id
WHERE rp.role_id = 3  -- Replace with actual role_id
  AND rp.can_view = 1
  AND ssm.is_active = 1
ORDER BY ssm.sidebar_menu_id, ssm.level;
```

## Comparison Testing

### Compare API with Admin Dashboard
1. Log in to admin dashboard as a specific staff member
2. Note which menu items are visible in the sidebar
3. Call the API with the same staff_id
4. Compare the API response with the dashboard sidebar
5. Verify they match

### Compare Before and After Fix
If you have the old code backed up:
1. Test with old code and save response
2. Test with new code and save response
3. Compare the submenu counts
4. Verify that new code properly filters submenus

## Performance Testing

### Response Time
- Superadmin request should complete in < 500ms
- Regular staff request should complete in < 500ms
- Multiple concurrent requests should not degrade performance

### Database Query Count
- Check database query logs
- Verify efficient use of JOINs
- Ensure no N+1 query problems

## Common Issues and Solutions

### Issue 1: All Submenus Still Showing
**Symptom**: Non-superadmin users see all submenus
**Solution**: 
- Verify the code changes were saved
- Clear any PHP opcode cache
- Restart web server

### Issue 2: No Submenus Showing
**Symptom**: Even superadmin sees no submenus
**Solution**:
- Check database connection
- Verify `sidebar_sub_menus` table has data
- Check `is_active` field values

### Issue 3: Permission Errors
**Symptom**: Staff with permissions see no menus
**Solution**:
- Verify `permission_group_id` is set correctly in database
- Check `roles_permissions` table has correct mappings
- Verify `can_view` is set to 1

## Test Data Setup

### Create Test Staff Members
```sql
-- Create a test accountant (if not exists)
INSERT INTO staff (name, surname, employee_id, is_active)
VALUES ('Test', 'Accountant', 'TEST001', 1);

-- Assign accountant role (role_id = 3)
INSERT INTO staff_roles (staff_id, role_id)
VALUES (LAST_INSERT_ID(), 3);
```

### Grant Specific Permissions
```sql
-- Grant specific menu permissions to a role
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete)
SELECT 3, id, 1, 0, 0, 0
FROM permission_category
WHERE short_code IN ('student_information', 'fees_collection');
```

## Reporting Issues

If you find any issues during testing:
1. Note the staff_id used
2. Save the full API response
3. Check the database for that staff's role and permissions
4. Document the expected vs actual behavior
5. Check server error logs for any PHP errors

## Success Criteria

The fix is successful if:
- ✅ Superadmin users see all menus and submenus
- ✅ Non-superadmin users see only permitted menus
- ✅ Non-superadmin users see only permitted submenus
- ✅ API response matches admin dashboard sidebar
- ✅ No security vulnerabilities (unauthorized access)
- ✅ Performance is acceptable (< 500ms response time)
- ✅ Error handling works correctly
- ✅ All test cases pass

## Next Steps After Testing

1. Document any issues found
2. Verify fix in production-like environment
3. Update API documentation if needed
4. Consider adding automated tests
5. Monitor production logs after deployment

