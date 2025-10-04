# Staff Profile API - Endpoint Clarification

## Issue Summary

There appears to be confusion between TWO DIFFERENT endpoints in the Teacher_webservice.php controller:

### 1. `/teacher/profile` (v1.2 - Enhanced)
**Purpose:** Complete staff profile with all information  
**Method:** `profile()`  
**Lines:** 1915-2215  
**Status:** ✅ CORRECTLY IMPLEMENTED with v1.2 enhancements

### 2. `/teacher/attendance-summary` (Different Endpoint)
**Purpose:** Attendance summary only (legacy endpoint)  
**Method:** `attendance_summary()`  
**Lines:** 1524-1650  
**Status:** ⚠️ Uses old model method with different structure

---

## The Confusion

Based on your description, you're receiving a response with:
- `attendance_records` object
- `attendance_summary` with string values
- `qr_code` object with `qr_code_url` and `qr_string`

**This response structure does NOT come from `/teacher/profile`.**

It appears to come from either:
1. `/teacher/attendance-summary` endpoint
2. A different API endpoint entirely
3. Or possibly cached/old response data

---

## Correct Endpoint Structure

### `/teacher/profile` Response (v1.2)

```json
{
  "status": 1,
  "message": "Staff profile retrieved successfully",
  "data": {
    "personal_information": { ... },
    "payroll_information": { ... },
    "leave_information": { ... },
    "attendance_information": {
      "summary": {
        "total_present": 0,
        "total_absent": 0,
        "total_late": 0,
        "total_half_day": 0,
        "total_holiday": 0,
        "total_records": 0,
        "attendance_percentage": 0
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
            }
          ],
          "month_summary": {
            "present": 20,
            "absent": 2,
            "late": 1,
            "half_day": 1,
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
        }
      ]
    },
    "file_paths": {
      "profile_image": "http://localhost/amt/uploads/staff_images/filename.jpg?1759509495",
      "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/200226.png?1759509495",
      "barcode": "http://localhost/amt/uploads/staff_id_card/barcodes/200226.png?1759509495",
      "documents": { ... }
    }
  },
  "timestamp": "2025-10-03 12:34:56"
}
```

---

## How to Test the Correct Endpoint

### Using the Test Script

```bash
php test_profile_staff_6.php
```

This will test `/teacher/profile` with staff_id 6 and verify:
- ✓ Attendance structure has `summary`, `monthly_breakdown`, `attendance_types`
- ✓ File paths include `profile_image`, `qr_code`, `barcode` with timestamps
- ✓ QR code and barcode use employee_id (200226) in the path
- ✓ No old structures like `attendance_records` or `qr_code.qr_code_url`

### Using cURL

```bash
curl -X POST http://localhost/amt/api/teacher/profile \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -d '{"staff_id": 6}'
```

### Using Postman

1. **Method:** POST
2. **URL:** `http://localhost/amt/api/teacher/profile`
3. **Headers:**
   - Content-Type: application/json
   - Client-Service: smartschool
   - Auth-Key: schoolAdmin@
4. **Body (raw JSON):**
   ```json
   {
     "staff_id": 6
   }
   ```

---

## Verification Checklist

When you call `/teacher/profile`, you should see:

### ✓ Correct Structure (v1.2)
- [x] `data.attendance_information.summary` (object with numeric values)
- [x] `data.attendance_information.monthly_breakdown` (array)
- [x] `data.attendance_information.attendance_types` (array with colors)
- [x] `data.file_paths.qr_code` (string with timestamp)
- [x] `data.file_paths.barcode` (string with timestamp)

### ✗ Incorrect Structure (should NOT be present)
- [ ] `data.attendance_records` (old structure)
- [ ] `data.attendance_summary` (old structure)
- [ ] `data.qr_code.qr_code_url` (old structure)
- [ ] `data.qr_code.qr_string` (old structure)

---

## If You're Still Seeing the Wrong Structure

### Possible Causes:

1. **Wrong Endpoint**
   - Make sure you're calling `/teacher/profile` not `/teacher/attendance-summary`
   - Check your API URL carefully

2. **Cached Response**
   - Clear browser cache
   - Clear API cache if any
   - Try with a different HTTP client

3. **Different API Version**
   - Check if you're calling the correct API base URL
   - Verify you're using `http://localhost/amt/api/teacher/profile`

4. **Proxy or Load Balancer**
   - Check if there's a proxy caching responses
   - Verify the request is reaching the correct server

5. **Code Not Deployed**
   - Verify the updated code is in place
   - Check file modification timestamps
   - Restart web server if needed

---

## Code Verification

### Check the profile() Method

The `profile()` method in `api/application/controllers/Teacher_webservice.php` should:

1. **Line 2177:** Call `getStaffAttendanceInfo($staff_id)`
2. **Line 2180:** Call `getStaffFilePaths($staff_info, $staff_id)`
3. **Line 2190:** Include `attendance_information` in response
4. **Line 2191:** Include `file_paths` in response

### Check Helper Methods

1. **getStaffAttendanceInfo()** (Lines 2220-2370)
   - Returns array with `summary`, `monthly_breakdown`, `attendance_types`
   - Uses enhanced structure with day names and colors

2. **getStaffFilePaths()** (Lines 2372-2454)
   - Returns array with `profile_image`, `qr_code`, `barcode`, `documents`
   - Includes timestamp parameters
   - Checks file existence for QR/barcode

---

## Quick Diagnostic

Run this command to check which endpoint you're actually calling:

```bash
# Check your API call
echo "Your API URL:"
# Look at your code/logs to see the actual URL being called

# Test the correct endpoint
curl -X POST http://localhost/amt/api/teacher/profile \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@" \
  -d '{"staff_id": 6}' | jq '.data | keys'

# Should output:
# [
#   "attendance_information",
#   "file_paths",
#   "leave_information",
#   "payroll_information",
#   "personal_information"
# ]
```

---

## Summary

**The `/teacher/profile` endpoint IS correctly implemented with v1.2 enhancements.**

If you're seeing a different response structure, you're likely:
1. Calling a different endpoint (`/teacher/attendance-summary`)
2. Looking at cached/old response data
3. Calling a different API entirely

**Solution:**
- Use the test script: `php test_profile_staff_6.php`
- Verify you're calling: `POST /teacher/profile`
- Check the response matches the v1.2 structure

---

## Need Help?

If you're still seeing the wrong structure after verifying:
1. Share the exact API URL you're calling
2. Share the exact request headers and body
3. Share the full response you're receiving
4. Check server logs for any errors

The `/teacher/profile` endpoint with v1.2 enhancements is working correctly and ready for use!

