# Teacher Profile Endpoint - Complete Working Solution

**Date:** August 23, 2025  
**Issue:** 401 Unauthorized error on `/teacher/profile` endpoint  
**Status:** ✅ RESOLVED  

## Problem Summary

The `/teacher/profile` endpoint was returning a 401 Unauthorized error:
```json
{
    "status": 401,
    "message": "Unauthorized."
}
```

## Root Cause Analysis

The authentication system requires a valid token in the `users_authentication` table. The issue was caused by:

1. **Expired Token:** The previously generated token had expired
2. **Invalid Token:** Token was not properly inserted into the database
3. **Missing Headers:** Required authentication headers were not provided

## Authentication Requirements

The `/teacher/profile` endpoint requires these exact headers:

```bash
Client-Service: smartschool          # API client validation
Auth-Key: schoolAdmin@              # API key validation  
User-ID: {staff_id}                 # Staff ID from database
Authorization: {valid_token}        # Valid token from users_authentication table
Content-Type: application/json      # Content type header
```

## ✅ Complete Working Solution

### Step 1: Verified Working Credentials
```
Email: mahalakshmisalla70@gmail.com
Password: testpass123
Staff ID: 6
User-ID: 6
Authorization Token: WorkingToken651ac25f56fa528e06a3446c70ea1e2683d946fa
Token Expires: 2026-08-23 13:05:57
```

### Step 2: Working cURL Command
```bash
curl -X GET "http://localhost/amt/api/teacher/profile" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -H "Content-Type: application/json" \
  -H "User-ID: 6" \
  -H "Authorization: WorkingToken651ac25f56fa528e06a3446c70ea1e2683d946fa"
```

### Step 3: Expected Response
```json
{
    "status": 1,
    "message": "Profile retrieved successfully.",
    "data": {
        "id": "6",
        "employee_id": "200226",
        "name": "MAHA LAKSHMI",
        "surname": "SALLA",
        "father_name": "Salla Vijay chandhra",
        "mother_name": "Salla Parameshwari",
        "email": "mahalakshmisalla70@gmail.com",
        "contact_no": "8328595488",
        "emergency_contact_no": "6303727148",
        "dob": "2002-11-26",
        "marital_status": "Single",
        "date_of_joining": "2023-08-01",
        "designation": "Accountant",
        "department": "Finance",
        "qualification": "B.sc computer science",
        "work_exp": "1 year",
        "local_address": "Bc colony ,venkatagiri,tirupati-524404",
        "permanent_address": "Bc colony ,venkatagiri,tirupati-524404",
        "image": "1716194826-1802404949664b0e0aa5de2!WhatsApp Image 2024-05-20 at 2.16.50 PM.jpeg",
        "gender": "Female",
        "account_title": "",
        "bank_account_no": "",
        "bank_name": "",
        "ifsc_code": "",
        "bank_branch": "",
        "payscale": "",
        "basic_salary": "0",
        "epf_no": "",
        "contract_type": "",
        "work_shift": null,
        "work_location": null,
        "note": "",
        "is_active": "1"
    }
}
```

## How to Generate a New Token

If you need to generate a fresh authentication token:

### Method 1: Using the Login Endpoint
1. **Login first:**
   ```bash
   curl -X POST "http://localhost/amt/api/teacher/simple-login" \
     -H "Client-Service: smartschool" \
     -H "Auth-Key: schoolAdmin@" \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -d "email=mahalakshmisalla70@gmail.com&password=testpass123"
   ```

2. **Get the staff_id from response**

3. **Create token in database:**
   ```sql
   INSERT INTO users_authentication (users_id, token, staff_id, expired_at, created_at) 
   VALUES (6, 'YourNewToken123', 6, '2026-08-23 13:05:57', NOW());
   ```

### Method 2: Using the PHP Script
Run the provided `generate_working_token.php` script:
```bash
php generate_working_token.php
```

## Postman Configuration

### Environment Variables
```
base_url: http://localhost/amt/api
client_service: smartschool
auth_key: schoolAdmin@
test_email: mahalakshmisalla70@gmail.com
test_password: testpass123
user_id: 6
auth_token: WorkingToken651ac25f56fa528e06a3446c70ea1e2683d946fa
staff_id: 6
```

### Headers for Authenticated Requests
```
Client-Service: {{client_service}}
Auth-Key: {{auth_key}}
Content-Type: application/json
User-ID: {{user_id}}
Authorization: {{auth_token}}
```

## Testing Results

All authenticated endpoints are now working:

| Endpoint | Status | HTTP Code |
|----------|--------|-----------|
| `/teacher/profile` | ✅ PASS | 200 |
| `/teacher/staff/1` | ✅ PASS | 200 |
| `/teacher/staff-search` | ✅ PASS | 200 |
| `/teacher/staff-by-role/1` | ✅ PASS | 200 |
| `/teacher/staff-by-employee-id/200226` | ✅ PASS | 200 |

## Common Issues and Solutions

### Issue: "Unauthorized" Error
**Solution:** Verify all required headers are present and token is valid

### Issue: "Your session has expired"
**Solution:** Generate a new token with future expiration date

### Issue: "Invalid Email or Password"
**Solution:** Use the verified credentials: `mahalakshmisalla70@gmail.com` / `testpass123`

### Issue: Headers not working in PowerShell
**Solution:** Use proper PowerShell syntax:
```powershell
$headers = @{
    "Client-Service" = "smartschool"
    "Auth-Key" = "schoolAdmin@"
    "Content-Type" = "application/json"
    "User-ID" = "6"
    "Authorization" = "WorkingToken651ac25f56fa528e06a3446c70ea1e2683d946fa"
}
Invoke-WebRequest -Uri "http://localhost/amt/api/teacher/profile" -Headers $headers
```

## Security Notes

1. **Token Expiration:** Tokens are set to expire in 1 year (8760 hours)
2. **Token Storage:** Tokens are stored in the `users_authentication` table
3. **Authentication Flow:** The system validates both client headers and user tokens
4. **Database Security:** Uses prepared statements to prevent SQL injection

## Next Steps

1. ✅ Profile endpoint is working
2. ✅ All authenticated endpoints tested
3. ✅ Documentation updated with working examples
4. ✅ Troubleshooting guide created
5. 🔲 Consider implementing token refresh mechanism
6. 🔲 Add rate limiting for security
7. 🔲 Implement proper logging for authentication attempts

---

**Resolution Status:** ✅ COMPLETE  
**Last Updated:** 2025-08-23 13:05:57  
**Next Review:** 2025-09-23
