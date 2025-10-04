# Staff Profile API Endpoint Documentation

## Overview
This document describes the comprehensive staff profile API endpoint that returns complete staff information including personal details, payroll records, leave records, attendance summary, and file paths.

## Endpoint Details

### URL
```
POST /teacher/profile
```

### Request Headers
```
Content-Type: application/json
Client-Service: smartschool
Auth-Key: schoolAdmin@
```

### Request Body
```json
{
  "staff_id": 2
}
```

### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| staff_id | integer | Yes | The ID of the staff member to retrieve |

---

## Response Structure

### Success Response (HTTP 200)

```json
{
  "status": 1,
  "message": "Staff profile retrieved successfully",
  "data": {
    "personal_information": { ... },
    "payroll_information": { ... },
    "leave_information": { ... },
    "attendance_information": { ... },
    "file_paths": { ... }
  },
  "timestamp": "2025-10-03 12:34:56"
}
```

---

## Data Sections

### 1. Personal Information

Contains complete staff personal and employment details:

```json
"personal_information": {
  "id": 2,
  "employee_id": "EMP001",
  "name": "John",
  "surname": "Doe",
  "full_name": "John Doe",
  "designation": "Senior Teacher",
  "department": "Mathematics",
  "phone": "1234567890",
  "email": "john.doe@school.com",
  "emergency_contact": "9876543210",
  "qualification": "M.Sc Mathematics",
  "work_experience": "5 years",
  "date_of_joining": "2020-01-15",
  "date_of_birth": "1990-05-20",
  "marital_status": "Married",
  "gender": "Male",
  "father_name": "Robert Doe",
  "mother_name": "Mary Doe",
  "local_address": "123 Main Street, City",
  "permanent_address": "456 Home Street, Town",
  "note": "Excellent teacher",
  "is_active": 1,
  "role": {
    "id": 2,
    "name": "Teacher",
    "is_superadmin": false
  },
  "bank_details": {
    "account_title": "John Doe",
    "bank_account_no": "1234567890",
    "bank_name": "ABC Bank",
    "ifsc_code": "ABCD0001234",
    "bank_branch": "Main Branch"
  },
  "employment_details": {
    "epf_no": "EPF123456",
    "basic_salary": 50000.00,
    "contract_type": "Permanent",
    "payscale": "Grade A",
    "shift": "Morning",
    "location": "Main Campus",
    "date_of_leaving": null
  },
  "social_media": {
    "facebook": "https://facebook.com/johndoe",
    "twitter": "https://twitter.com/johndoe",
    "linkedin": "https://linkedin.com/in/johndoe",
    "instagram": "https://instagram.com/johndoe"
  }
}
```

### 2. Payroll Information

Complete payroll history with summary:

```json
"payroll_information": {
  "records": [
    {
      "id": 1,
      "month": "October",
      "year": "2024",
      "basic_salary": 50000.00,
      "total_allowance": 10000.00,
      "total_deduction": 5000.00,
      "leave_deduction": 2,
      "tax": "2000.00",
      "net_salary": 53000.00,
      "status": "paid",
      "payment_mode": "Bank Transfer",
      "payment_date": "2024-10-31",
      "remark": "Regular payment",
      "created_at": "2024-10-31 10:00:00"
    }
  ],
  "summary": {
    "total_records": 12,
    "total_net_salary": 636000.00,
    "total_allowances": 120000.00,
    "total_deductions": 60000.00,
    "total_tax": 24000.00
  }
}
```

### 3. Leave Information

All leave requests with detailed summary:

```json
"leave_information": {
  "records": [
    {
      "id": 1,
      "leave_type": "Sick Leave",
      "leave_type_id": 1,
      "leave_from": "2024-10-01",
      "leave_to": "2024-10-03",
      "leave_days": 3,
      "employee_remark": "Medical emergency",
      "admin_remark": "Approved",
      "status": "approve",
      "applied_by": "John Doe (EMP001)",
      "applied_by_id": 2,
      "document_file": "medical_certificate.pdf",
      "apply_date": "2024-09-28",
      "created_at": "2024-09-28 09:00:00"
    }
  ],
  "summary": {
    "total_requests": 5,
    "approved_count": 4,
    "pending_count": 1,
    "disapproved_count": 0,
    "total_leave_days": 15,
    "approved_leave_days": 12
  }
}
```

### 4. Attendance Information

Comprehensive attendance data with monthly breakdown and calendar-like structure:

```json
"attendance_information": {
  "summary": {
    "total_present": 180,
    "total_absent": 5,
    "total_late": 3,
    "total_half_day": 2,
    "total_holiday": 0,
    "total_records": 190,
    "attendance_percentage": 94.74
  },
  "monthly_breakdown": [
    {
      "month": "October",
      "year": "2024",
      "days": [
        {
          "date": "2024-10-15",
          "day_name": "Tuesday",
          "status": "present",
          "status_key": "P",
          "remark": ""
        },
        {
          "date": "2024-10-14",
          "day_name": "Monday",
          "status": "late",
          "status_key": "L",
          "remark": "Arrived 30 minutes late"
        },
        {
          "date": "2024-10-13",
          "day_name": "Sunday",
          "status": "absent",
          "status_key": "A",
          "remark": "Sick leave"
        },
        {
          "date": "2024-10-12",
          "day_name": "Saturday",
          "status": "half_day",
          "status_key": "H",
          "remark": "Left early for personal work"
        }
      ],
      "month_summary": {
        "present": 20,
        "absent": 2,
        "late": 1,
        "half_day": 1,
        "holiday": 0
      }
    },
    {
      "month": "September",
      "year": "2024",
      "days": [
        {
          "date": "2024-09-30",
          "day_name": "Monday",
          "status": "present",
          "status_key": "P",
          "remark": ""
        }
      ],
      "month_summary": {
        "present": 22,
        "absent": 1,
        "late": 0,
        "half_day": 0,
        "holiday": 0
      }
    }
  ],
  "attendance_types": [
    {
      "id": 1,
      "type": "Present",
      "key_value": "P",
      "color": "#4CAF50"
    },
    {
      "id": 2,
      "type": "Late",
      "key_value": "L",
      "color": "#FF9800"
    },
    {
      "id": 3,
      "type": "Absent",
      "key_value": "A",
      "color": "#F44336"
    },
    {
      "id": 4,
      "type": "Half Day",
      "key_value": "H",
      "color": "#2196F3"
    },
    {
      "id": 5,
      "type": "Holiday",
      "key_value": "F",
      "color": "#9C27B0"
    }
  ]
}
```

**Note**:
- Monthly breakdown is ordered by most recent month first
- Days within each month are ordered by most recent date first
- Attendance percentage calculation: `(total_present + (total_half_day * 0.5)) / total_records * 100`
- Each day includes the day name (Monday, Tuesday, etc.) for easy calendar display
- Remarks are included for each attendance record

### 5. File Paths

All document and image paths:

```json
"file_paths": {
  "profile_image": "http://localhost/amt/uploads/staff_images/staff_123.jpg",
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png",
  "barcode": "http://localhost/amt/uploads/staff_id_card/barcodes/EMP001.png",
  "documents": {
    "resume": {
      "filename": "resume.pdf",
      "path": "http://localhost/amt/uploads/staff_documents/2/resume.pdf",
      "type": "resume"
    },
    "joining_letter": {
      "filename": "joining_letter.pdf",
      "path": "http://localhost/amt/uploads/staff_documents/2/joining_letter.pdf",
      "type": "joining_letter"
    },
    "resignation_letter": {
      "filename": "resignation_letter.pdf",
      "path": "http://localhost/amt/uploads/staff_documents/2/resignation_letter.pdf",
      "type": "resignation_letter"
    },
    "other_document": {
      "filename": "certificate.pdf",
      "name": "Teaching Certificate",
      "path": "http://localhost/amt/uploads/staff_documents/2/certificate.pdf",
      "type": "other_document"
    }
  }
}
```

---

## Error Responses

### Invalid Request Method (HTTP 400)
```json
{
  "status": 0,
  "message": "Bad request. Only POST method allowed.",
  "timestamp": "2025-10-03 12:34:56"
}
```

### Missing staff_id (HTTP 400)
```json
{
  "status": 0,
  "message": "staff_id is required in request body",
  "example": {
    "staff_id": 2
  },
  "timestamp": "2025-10-03 12:34:56"
}
```

### Invalid staff_id (HTTP 400)
```json
{
  "status": 0,
  "message": "staff_id must be a valid positive integer",
  "provided": "abc",
  "timestamp": "2025-10-03 12:34:56"
}
```

### Staff Not Found (HTTP 404)
```json
{
  "status": 0,
  "message": "Staff member not found",
  "staff_id": 999,
  "timestamp": "2025-10-03 12:34:56"
}
```

### Server Error (HTTP 500)
```json
{
  "status": 0,
  "message": "Exception occurred while retrieving staff profile",
  "error": {
    "type": "Exception",
    "message": "Database connection failed",
    "file": "Teacher_webservice.php",
    "line": 1234
  },
  "staff_id": 2,
  "timestamp": "2025-10-03 12:34:56"
}
```

---

## Testing

### Using cURL
```bash
curl -X POST http://localhost/amt/api/teacher/profile \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -d '{"staff_id": 2}'
```

### Using PHP Test Script
```bash
php test_staff_profile_api.php
```

### Using Postman
1. Method: POST
2. URL: `http://localhost/amt/api/teacher/profile`
3. Headers:
   - Content-Type: application/json
   - Client-Service: smartschool
   - Auth-Key: schoolAdmin@
4. Body (raw JSON):
   ```json
   {
     "staff_id": 2
   }
   ```

---

## Implementation Details

### Database Tables Used
- `staff` - Main staff information
- `staff_designation` - Designation details
- `department` - Department details
- `staff_roles` - Staff role mapping
- `roles` - Role information
- `staff_payslip` - Payroll records
- `staff_leave_request` - Leave requests
- `leave_types` - Leave type details
- `staff_attendance` - Attendance records
- `staff_attendance_type` - Attendance type mapping

### Models Used
- `staff_model` - Staff data operations
- `staffattendancemodel` - Attendance operations
- `leaverequest_model` - Leave request operations

### Key Features
1. **Comprehensive Data**: Returns all staff-related information in a single API call
2. **Efficient Queries**: Uses optimized database joins to minimize queries
3. **Error Handling**: Proper validation and error responses
4. **Structured Response**: Well-organized JSON structure for easy parsing
5. **Summary Statistics**: Includes calculated summaries for payroll, leave, and attendance
6. **File Paths**: Complete URLs for all documents and images

---

## Notes

1. The endpoint follows the existing authentication pattern used in other Teacher_webservice endpoints
2. All monetary values are returned as floats
3. Dates are returned in YYYY-MM-DD format
4. The attendance percentage is calculated based on present days vs total records
5. Profile images default to gender-specific defaults if not uploaded
6. QR code and barcode paths are generated based on employee_id

---

## Version History

- **v1.0** (2025-10-03): Initial implementation with complete staff profile data

