# Staff Profile API - Quick Reference Guide

## Endpoint
```
POST /teacher/profile
```

## Request
```json
{
  "staff_id": 2
}
```

## Headers
```
Content-Type: application/json
Client-Service: smartschool
Auth-Key: schoolAdmin@
```

## Response Sections

### 1. Personal Information
```javascript
data.personal_information.employee_id
data.personal_information.full_name
data.personal_information.designation
data.personal_information.department
data.personal_information.phone
data.personal_information.email
data.personal_information.emergency_contact
data.personal_information.qualification
data.personal_information.work_experience
data.personal_information.date_of_joining
data.personal_information.marital_status
data.personal_information.father_name
data.personal_information.mother_name
data.personal_information.local_address
data.personal_information.permanent_address
data.personal_information.role.name
data.personal_information.bank_details.bank_account_no
data.personal_information.employment_details.basic_salary
```

### 2. Payroll Information
```javascript
data.payroll_information.records[]
  - id
  - month
  - year
  - basic_salary
  - total_allowance
  - total_deduction
  - net_salary
  - status
  - payment_mode
  - payment_date

data.payroll_information.summary
  - total_records
  - total_net_salary
  - total_allowances
  - total_deductions
  - total_tax
```

### 3. Leave Information
```javascript
data.leave_information.records[]
  - id
  - leave_type
  - leave_from
  - leave_to
  - leave_days
  - status
  - employee_remark
  - admin_remark
  - applied_by

data.leave_information.summary
  - total_requests
  - approved_count
  - pending_count
  - disapproved_count
  - total_leave_days
  - approved_leave_days
```

### 4. Attendance Information
```javascript
// Summary
data.attendance_information.summary.total_present
data.attendance_information.summary.total_absent
data.attendance_information.summary.total_late
data.attendance_information.summary.total_half_day
data.attendance_information.summary.total_holiday
data.attendance_information.summary.total_records
data.attendance_information.summary.attendance_percentage

// Monthly Breakdown
data.attendance_information.monthly_breakdown[]
  - month (e.g., "October")
  - year (e.g., "2024")
  - days[]
    - date (e.g., "2024-10-15")
    - day_name (e.g., "Tuesday")
    - status (e.g., "present", "late", "absent", "half_day")
    - status_key (e.g., "P", "L", "A", "H")
    - remark
  - month_summary
    - present
    - absent
    - late
    - half_day
    - holiday

// Attendance Types
data.attendance_information.attendance_types[]
  - id
  - type
  - key_value
  - color
```

### 5. File Paths
```javascript
data.file_paths.profile_image
data.file_paths.qr_code
data.file_paths.barcode
data.file_paths.documents.resume.path
data.file_paths.documents.joining_letter.path
data.file_paths.documents.resignation_letter.path
data.file_paths.documents.other_document.path
```

## Example Usage (JavaScript/Fetch)
```javascript
fetch('http://localhost/amt/api/teacher/profile', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Client-Service': 'smartschool',
    'Auth-Key': 'schoolAdmin@'
  },
  body: JSON.stringify({ staff_id: 2 })
})
.then(response => response.json())
.then(data => {
  if (data.status === 1) {
    console.log('Staff Name:', data.data.personal_information.full_name);
    console.log('Total Payroll Records:', data.data.payroll_information.summary.total_records);
    console.log('Attendance %:', data.data.attendance_information.attendance_percentage);
  }
});
```

## Example Usage (PHP/cURL)
```php
$ch = curl_init('http://localhost/amt/api/teacher/profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['staff_id' => 2]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['status'] == 1) {
    echo "Staff: " . $data['data']['personal_information']['full_name'];
    echo "\nDesignation: " . $data['data']['personal_information']['designation'];
    echo "\nAttendance: " . $data['data']['attendance_information']['attendance_percentage'] . "%";
}
```

## Common Response Codes
- **200**: Success
- **400**: Bad request (invalid input)
- **404**: Staff not found
- **500**: Server error

## Quick Tips
1. Always validate staff_id exists before calling
2. Check `status` field in response (1 = success, 0 = error)
3. Handle empty arrays for records (staff may have no payroll/leave/attendance)
4. Profile image falls back to default if not uploaded
5. All monetary values are floats
6. All dates are in YYYY-MM-DD format
7. Attendance percentage is calculated as (present / total_records) * 100

