# Staff Profile API Implementation

## 📋 Overview

This implementation provides a comprehensive REST API endpoint for retrieving complete staff profile information in a single API call. The endpoint returns personal information, payroll records, leave history, attendance data, and file paths.

## 🎯 Features

### ✅ Complete Implementation
- **Personal Information**: All staff details including contact, qualification, employment info
- **Payroll Records**: Complete payroll history with summary statistics
- **Leave Records**: All leave requests with approval status and summaries
- **Attendance Data**: Comprehensive attendance with date arrays and percentages
- **File Paths**: URLs for profile images, QR codes, barcodes, and documents

### ✅ Production Ready
- Proper error handling and validation
- Follows existing codebase patterns
- Efficient database queries with joins
- Well-structured JSON responses
- Complete documentation

## 🚀 Quick Start

### Endpoint
```
POST /teacher/profile
```

### Request
```bash
curl -X POST http://localhost/amt/api/teacher/profile \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -d '{"staff_id": 2}'
```

### Response
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

## 📁 Files

### Modified Files
- `api/application/controllers/Teacher_webservice.php` - Added profile() method and helpers

### Documentation Files
- `STAFF_PROFILE_API_DOCUMENTATION.md` - Complete API documentation
- `STAFF_PROFILE_API_IMPLEMENTATION_SUMMARY.md` - Implementation details
- `STAFF_PROFILE_API_QUICK_REFERENCE.md` - Quick reference guide
- `README_STAFF_PROFILE_API.md` - This file

### Test Files
- `test_staff_profile_api.php` - PHP test script

## 🧪 Testing

### Method 1: PHP Test Script
```bash
php test_staff_profile_api.php
```

### Method 2: cURL Command
```bash
curl -X POST http://localhost/amt/api/teacher/profile \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -d '{"staff_id": 2}'
```

### Method 3: Postman
1. Create POST request to `http://localhost/amt/api/teacher/profile`
2. Add headers:
   - Content-Type: application/json
   - Client-Service: smartschool
   - Auth-Key: schoolAdmin@
3. Body (raw JSON): `{"staff_id": 2}`

## 📊 Data Structure

### Personal Information
- Employee ID, Name, Designation, Department
- Contact details (phone, email, emergency contact)
- Qualification and work experience
- Dates (joining, birth)
- Family information (father, mother names)
- Addresses (local, permanent)
- Role and permissions
- Bank account details
- Employment details (salary, contract, shift)
- Social media links

### Payroll Information
- Complete payroll history
- Monthly salary breakdowns
- Allowances and deductions
- Tax information
- Payment status and modes
- Summary statistics

### Leave Information
- All leave requests
- Leave types and dates
- Approval status
- Employee and admin remarks
- Applied by information
- Document attachments
- Summary statistics

### Attendance Information
- Total present/absent/late counts
- Date arrays for each status
- Attendance percentage
- Complete attendance history

### File Paths
- Profile image URL
- QR code URL
- Barcode URL
- Document URLs (resume, letters, certificates)

## 🔧 Technical Details

### Database Tables
- staff
- staff_designation
- department
- staff_roles
- roles
- staff_payslip
- staff_leave_request
- leave_types
- staff_attendance
- staff_attendance_type

### Models Used
- staff_model
- staffattendancemodel
- leaverequest_model

### Key Methods
- `profile()` - Main endpoint method
- `getStaffAttendanceInfo()` - Helper for attendance data
- `getStaffFilePaths()` - Helper for file paths

## 🛡️ Security

- Input validation (staff_id must be positive integer)
- SQL injection prevention (Query Builder)
- Authentication headers required
- Error message safety
- Database error handling

## 📖 Documentation

### Full Documentation
See `STAFF_PROFILE_API_DOCUMENTATION.md` for:
- Complete request/response examples
- All error codes and messages
- Field descriptions
- Testing instructions

### Quick Reference
See `STAFF_PROFILE_API_QUICK_REFERENCE.md` for:
- Quick access to field paths
- Code examples
- Common patterns

### Implementation Details
See `STAFF_PROFILE_API_IMPLEMENTATION_SUMMARY.md` for:
- Technical implementation details
- Database queries
- Code structure
- Performance considerations

## 💡 Usage Examples

### JavaScript (Fetch API)
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
    const profile = data.data.personal_information;
    console.log(`${profile.full_name} - ${profile.designation}`);
  }
});
```

### PHP (cURL)
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
    $profile = $data['data']['personal_information'];
    echo "{$profile['full_name']} - {$profile['designation']}";
}
```

## ⚠️ Error Handling

### Common Errors
- **400**: Invalid request (missing/invalid staff_id)
- **404**: Staff not found
- **500**: Server error

### Error Response Format
```json
{
  "status": 0,
  "message": "Error description",
  "timestamp": "2025-10-03 12:34:56"
}
```

## 🎨 Response Format

All responses follow this structure:
```json
{
  "status": 1 or 0,
  "message": "Description",
  "data": { ... } or "error": { ... },
  "timestamp": "YYYY-MM-DD HH:MM:SS"
}
```

## 📝 Notes

1. All monetary values are returned as floats
2. All dates are in YYYY-MM-DD format
3. Profile images default to gender-specific defaults if not uploaded
4. QR codes and barcodes are based on employee_id
5. Attendance percentage = (present / total_records) * 100
6. Payroll summary only includes "paid" records

## 🔄 Version History

- **v1.0** (2025-10-03): Initial implementation

## 👥 Support

For issues or questions:
1. Check the documentation files
2. Review the test script output
3. Verify database tables and data
4. Check server logs for errors

## ✅ Checklist

- [x] Personal information endpoint
- [x] Payroll records with summary
- [x] Leave records with summary
- [x] Attendance data with arrays
- [x] File paths (images, documents)
- [x] Error handling
- [x] Input validation
- [x] Documentation
- [x] Test script
- [x] Code follows existing patterns

## 🎉 Success!

The Staff Profile API endpoint is fully implemented and ready for use. All requirements have been met:

✅ Staff Personal Information  
✅ Payroll Information  
✅ Leave Information  
✅ Attendance Information  
✅ File Paths  
✅ Error Handling  
✅ Documentation  
✅ Testing Tools  

Happy coding! 🚀

