# File Paths Enhancement - Update Documentation

## Overview
Fixed and enhanced the file path generation in the Staff Profile API to include proper timestamp parameters for cache busting and file existence checks.

---

## What Changed

### Previous Implementation
```php
// Simple path generation without timestamp
$qr_code_path = $base_url . 'uploads/staff_id_card/qrcode/' . $staff_info->employee_id . '.png';
$barcode_path = $base_url . 'uploads/staff_id_card/barcodes/' . $staff_info->employee_id . '.png';
$profile_image = $base_url . 'uploads/staff_images/' . $staff_info->image;
```

**Issues:**
- No timestamp parameter for cache busting
- No file existence checks for QR code and barcode
- Could return paths to non-existent files

### New Implementation
```php
// Enhanced path generation with timestamp and file checks
$timestamp = '?' . time();

// Profile image with timestamp
$profile_image = $base_url . 'uploads/staff_images/' . $staff_info->image . $timestamp;

// QR code with file existence check
if (file_exists('./uploads/staff_id_card/qrcode/' . $staff_info->employee_id . '.png')) {
    $qr_code_path = $base_url . 'uploads/staff_id_card/qrcode/' . $staff_info->employee_id . '.png' . $timestamp;
}

// Barcode with file existence check
if (file_exists('./uploads/staff_id_card/barcodes/' . $staff_info->employee_id . '.png')) {
    $barcode_path = $base_url . 'uploads/staff_id_card/barcodes/' . $staff_info->employee_id . '.png' . $timestamp;
}
```

**Improvements:**
- ✅ Timestamp parameter added for cache busting
- ✅ File existence checks before returning paths
- ✅ Returns empty string if file doesn't exist
- ✅ Consistent with existing codebase patterns

---

## New Features

### 1. Timestamp Parameter ✓
All image paths now include a timestamp parameter for cache busting:

**Example:**
```
https://amaravathijuniorcollege.com/uploads/staff_id_card/barcodes/200226.png?1759509495
```

**Benefits:**
- Prevents browser caching issues
- Ensures latest image is always displayed
- Matches the pattern used throughout the application

### 2. File Existence Checks ✓
QR code and barcode paths are only returned if the files actually exist:

**Before:**
```json
{
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png",
  "barcode": "http://localhost/amt/uploads/staff_id_card/barcodes/EMP001.png"
}
```
*Could return 404 if files don't exist*

**After:**
```json
{
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png?1759509495",
  "barcode": "http://localhost/amt/uploads/staff_id_card/barcodes/EMP001.png?1759509495"
}
```
*Or empty strings if files don't exist*

### 3. Default Images with Timestamp ✓
Default profile images also include timestamp:

```json
{
  "profile_image": "http://localhost/amt/uploads/staff_images/default_male.jpg?1759509495"
}
```

---

## Response Examples

### Complete File Paths Response

```json
"file_paths": {
  "profile_image": "http://localhost/amt/uploads/staff_images/staff_123.jpg?1759509495",
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png?1759509495",
  "barcode": "http://localhost/amt/uploads/staff_id_card/barcodes/EMP001.png?1759509495",
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

### When QR Code or Barcode Don't Exist

```json
"file_paths": {
  "profile_image": "http://localhost/amt/uploads/staff_images/default_male.jpg?1759509495",
  "qr_code": "",
  "barcode": "",
  "documents": {}
}
```

---

## Technical Details

### Timestamp Generation
The timestamp is generated using PHP's `time()` function:

```php
$timestamp = '?' . time();
```

This returns the current Unix timestamp (seconds since January 1, 1970), which:
- Changes every second
- Ensures unique URLs for cache busting
- Matches the `img_time()` helper function used throughout the application

### File Existence Check
Files are checked using PHP's `file_exists()` function:

```php
$qr_file = './uploads/staff_id_card/qrcode/' . $staff_info->employee_id . '.png';
if (file_exists($qr_file)) {
    $qr_code_path = $base_url . 'uploads/staff_id_card/qrcode/' . $staff_info->employee_id . '.png' . $timestamp;
}
```

**Note:** The relative path `./uploads/` is used for file existence checks, while the full URL with `base_url()` is returned in the response.

### Path Structure

| File Type | Directory | Filename Pattern | Example |
|-----------|-----------|------------------|---------|
| Profile Image | uploads/staff_images/ | {image_name} | staff_123.jpg |
| Default Male | uploads/staff_images/ | default_male.jpg | default_male.jpg |
| Default Female | uploads/staff_images/ | default_female.jpg | default_female.jpg |
| QR Code | uploads/staff_id_card/qrcode/ | {employee_id}.png | EMP001.png |
| Barcode | uploads/staff_id_card/barcodes/ | {employee_id}.png | EMP001.png |
| Documents | uploads/staff_documents/{staff_id}/ | {filename} | resume.pdf |

---

## Benefits

### 1. Cache Busting
- Browser won't cache old images
- Users always see the latest version
- Important for profile images that may be updated

### 2. Error Prevention
- No 404 errors for missing QR codes or barcodes
- Client can check if path is empty before displaying
- Better user experience

### 3. Consistency
- Matches the pattern used in the main application
- Uses the same timestamp approach as `img_time()` helper
- Follows existing codebase conventions

### 4. Reliability
- Only returns paths to files that actually exist
- Reduces client-side error handling
- More predictable API behavior

---

## Client-Side Usage

### JavaScript Example

```javascript
const filePaths = data.data.file_paths;

// Check if QR code exists before displaying
if (filePaths.qr_code) {
  document.getElementById('qr-code').src = filePaths.qr_code;
} else {
  document.getElementById('qr-code').style.display = 'none';
}

// Check if barcode exists before displaying
if (filePaths.barcode) {
  document.getElementById('barcode').src = filePaths.barcode;
} else {
  document.getElementById('barcode').style.display = 'none';
}

// Profile image always exists (defaults to gender-based image)
document.getElementById('profile-image').src = filePaths.profile_image;
```

### PHP Example

```php
$file_paths = $data['data']['file_paths'];

// Display QR code only if it exists
if (!empty($file_paths['qr_code'])) {
    echo '<img src="' . $file_paths['qr_code'] . '" alt="QR Code">';
}

// Display barcode only if it exists
if (!empty($file_paths['barcode'])) {
    echo '<img src="' . $file_paths['barcode'] . '" alt="Barcode">';
}

// Profile image always available
echo '<img src="' . $file_paths['profile_image'] . '" alt="Profile">';
```

---

## Migration Notes

### For Existing Integrations

If you were using the old file paths, the changes are backward compatible:

**Old Response:**
```json
{
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png"
}
```

**New Response:**
```json
{
  "qr_code": "http://localhost/amt/uploads/staff_id_card/qrcode/EMP001.png?1759509495"
}
```

**Impact:**
- The URL structure is the same, just with an added query parameter
- Browsers will treat it as a different URL (good for cache busting)
- No code changes required on the client side
- Images will load correctly with or without the timestamp

**Recommendation:**
- Update client code to check for empty strings before displaying QR/barcode
- This prevents 404 errors for staff without generated codes

---

## Testing

### Test Cases

1. **Staff with all files:**
   - Profile image: ✓ Returns with timestamp
   - QR code: ✓ Returns with timestamp
   - Barcode: ✓ Returns with timestamp
   - Documents: ✓ Returns all available

2. **Staff without profile image:**
   - Profile image: ✓ Returns default based on gender with timestamp
   - QR code: ✓ Returns with timestamp if exists, empty if not
   - Barcode: ✓ Returns with timestamp if exists, empty if not

3. **Staff without QR/Barcode:**
   - Profile image: ✓ Returns with timestamp
   - QR code: ✓ Returns empty string
   - Barcode: ✓ Returns empty string

4. **Cache busting:**
   - Each request: ✓ Returns different timestamp
   - Browser: ✓ Fetches fresh image

### Test the API

```bash
php test_staff_profile_api.php
```

Check the response for:
- Timestamp parameter on all image URLs
- Empty strings for non-existent QR/barcode files
- Proper URL format

---

## Code Location

**File:** `api/application/controllers/Teacher_webservice.php`  
**Method:** `getStaffFilePaths($staff_info, $staff_id)`  
**Lines:** 2372-2454

---

## Summary

✅ **Timestamp Parameter** - Added to all image URLs for cache busting  
✅ **File Existence Checks** - QR code and barcode only returned if files exist  
✅ **Empty String Handling** - Returns empty string instead of invalid paths  
✅ **Default Images** - Include timestamp parameter  
✅ **Backward Compatible** - No breaking changes to existing integrations  
✅ **Consistent Pattern** - Matches existing codebase conventions  
✅ **Better UX** - Prevents 404 errors and caching issues  

The file paths now match the format used throughout the application, including the timestamp parameter for cache busting, just like the example URL:
```
https://amaravathijuniorcollege.com/uploads/staff_id_card/barcodes/200226.png?1759509495
```

---

**Update Date:** October 3, 2025  
**Version:** 1.2  
**Status:** Complete ✅

