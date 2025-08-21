# Teacher Authentication API Documentation

## Overview

The Teacher Authentication API provides secure authentication and profile management for teachers in the Smart School Management System. It supports both traditional token-based authentication and modern JWT (JSON Web Token) authentication.

## Base URL
```
http://your-domain.com/api/
```

## Authentication Headers

All API requests require the following headers:

```
Client-Service: smartschool
Auth-Key: schoolAdmin@
Content-Type: application/json
```

For authenticated endpoints, also include:
```
User-ID: {user_id}
Authorization: {token}
JWT-Token: {jwt_token} (optional, for JWT authentication)
```

## Endpoints

### 1. Teacher Login

**Endpoint:** `POST /teacher/login`

**Description:** Authenticate a teacher and receive authentication tokens.

**Request Body:**
```json
{
    "email": "teacher@school.com",
    "password": "teacher_password",
    "deviceToken": "optional_device_token"
}
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Successfully logged in.",
    "id": 123,
    "token": "simple_auth_token",
    "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "role": "teacher",
    "record": {
        "id": 456,
        "staff_id": 456,
        "employee_id": "EMP001",
        "role": "teacher",
        "email": "teacher@school.com",
        "contact_no": "1234567890",
        "username": "John Doe",
        "name": "John",
        "surname": "Doe",
        "designation": "Mathematics Teacher",
        "department": "Science",
        "date_format": "d-m-Y",
        "currency_symbol": "$",
        "currency_short_name": "USD",
        "currency_id": 1,
        "timezone": "UTC",
        "sch_name": "Smart School",
        "language": {
            "lang_id": 1,
            "language": "English",
            "short_code": "en"
        },
        "is_rtl": "0",
        "theme": "default.jpg",
        "image": "teacher_photo.jpg",
        "start_week": "Monday",
        "superadmin_restriction": "0"
    }
}
```

**Error Responses:**
- `400`: Bad request (missing email/password)
- `401`: Invalid credentials
- `403`: Account disabled

### 2. Teacher Logout

**Endpoint:** `POST /teacher/logout`

**Description:** Logout teacher and invalidate tokens.

**Headers Required:**
```
User-ID: {user_id}
Authorization: {token}
```

**Request Body:**
```json
{
    "deviceToken": "optional_device_token"
}
```

**Success Response (200):**
```json
{
    "status": 200,
    "message": "Successfully logged out."
}
```

### 3. Get Teacher Profile

**Endpoint:** `GET /teacher/profile`

**Description:** Retrieve authenticated teacher's profile information.

**Headers Required:**
```
User-ID: {user_id}
Authorization: {token}
JWT-Token: {jwt_token} (alternative to User-ID/Authorization)
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Profile retrieved successfully.",
    "data": {
        "id": 456,
        "employee_id": "EMP001",
        "name": "John",
        "surname": "Doe",
        "father_name": "Robert Doe",
        "mother_name": "Mary Doe",
        "email": "teacher@school.com",
        "contact_no": "1234567890",
        "emergency_contact_no": "0987654321",
        "dob": "1985-05-15",
        "marital_status": "Married",
        "date_of_joining": "2020-01-15",
        "designation": "Mathematics Teacher",
        "department": "Science Department",
        "qualification": "M.Sc Mathematics",
        "work_exp": "5 years",
        "local_address": "123 Main St, City",
        "permanent_address": "456 Home St, Town",
        "image": "teacher_photo.jpg",
        "gender": "Male",
        "account_title": "John Doe",
        "bank_account_no": "1234567890",
        "bank_name": "ABC Bank",
        "ifsc_code": "ABC123456",
        "bank_branch": "Main Branch",
        "payscale": "Grade A",
        "basic_salary": 50000,
        "epf_no": "EPF123456",
        "contract_type": "Permanent",
        "work_shift": "Morning",
        "work_location": "Main Campus",
        "note": "Excellent teacher",
        "is_active": 1
    }
}
```

### 4. Update Teacher Profile

**Endpoint:** `PUT /teacher/profile/update`

**Description:** Update teacher's profile information.

**Headers Required:**
```
User-ID: {user_id}
Authorization: {token}
JWT-Token: {jwt_token} (alternative)
```

**Request Body:**
```json
{
    "name": "John",
    "surname": "Doe",
    "father_name": "Robert Doe",
    "mother_name": "Mary Doe",
    "contact_no": "1234567890",
    "emergency_contact_no": "0987654321",
    "local_address": "123 New Main St, City",
    "permanent_address": "456 New Home St, Town",
    "qualification": "M.Sc Mathematics, B.Ed",
    "work_exp": "6 years",
    "note": "Updated profile information",
    "account_title": "John Doe",
    "bank_account_no": "1234567890",
    "bank_name": "XYZ Bank",
    "ifsc_code": "XYZ123456",
    "bank_branch": "Central Branch"
}
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Profile updated successfully."
}
```

### 5. Change Password

**Endpoint:** `PUT /teacher/change-password`

**Description:** Change teacher's password.

**Headers Required:**
```
User-ID: {user_id}
Authorization: {token}
JWT-Token: {jwt_token} (alternative)
```

**Request Body:**
```json
{
    "current_password": "old_password",
    "new_password": "new_secure_password"
}
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Password changed successfully."
}
```

**Error Response:**
```json
{
    "status": 0,
    "message": "Current password is incorrect."
}
```

### 6. Get Dashboard Data

**Endpoint:** `GET /teacher/dashboard`

**Description:** Get teacher's dashboard information including assigned classes and subjects.

**Headers Required:**
```
User-ID: {user_id}
Authorization: {token}
JWT-Token: {jwt_token} (alternative)
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Dashboard data retrieved successfully.",
    "data": {
        "teacher_info": {
            "name": "John Doe",
            "employee_id": "EMP001",
            "designation": "Mathematics Teacher",
            "department": "Science Department",
            "email": "teacher@school.com",
            "image": "teacher_photo.jpg"
        },
        "assigned_classes": [
            {
                "class": "10",
                "section": "A",
                "session_id": 1
            },
            {
                "class": "9",
                "section": "B",
                "session_id": 1
            }
        ],
        "assigned_subjects": [
            {
                "subject_name": "Mathematics",
                "subject_code": "MATH"
            },
            {
                "subject_name": "Physics",
                "subject_code": "PHY"
            }
        ],
        "total_classes": 2,
        "total_subjects": 2
    }
}
```

### 7. Refresh JWT Token

**Endpoint:** `POST /teacher/refresh-token`

**Description:** Refresh an existing JWT token to extend its validity.

**Request Body:**
```json
{
    "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Token refreshed successfully.",
    "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 31536000
}
```

**Error Response:**
```json
{
    "status": 0,
    "message": "Invalid or expired token. Please login again."
}
```

### 8. Validate JWT Token

**Endpoint:** `POST /teacher/validate-token`

**Description:** Validate a JWT token and get its information.

**Request Body:**
```json
{
    "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Success Response (200):**
```json
{
    "status": 1,
    "message": "Token is valid.",
    "payload": {
        "user_id": 123,
        "staff_id": 456,
        "email": "teacher@school.com",
        "role": "teacher",
        "employee_id": "EMP001",
        "name": "John Doe",
        "iat": 1640995200,
        "exp": 1672531200,
        "iss": "smartschool-api"
    },
    "remaining_time": 2592000,
    "expires_in_hours": 720.0,
    "is_expiring_soon": false
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 400  | Bad Request - Invalid parameters |
| 401  | Unauthorized - Invalid credentials or token |
| 403  | Forbidden - Access denied |
| 404  | Not Found - Resource not found |
| 429  | Too Many Requests - Rate limit exceeded |
| 500  | Internal Server Error |

## Common Error Response Format

```json
{
    "status": 0,
    "message": "Error description"
}
```

## Authentication Types

### 1. Traditional Token Authentication
- Use `User-ID` and `Authorization` headers
- Tokens expire after 8760 hours (1 year)
- Stored in database for validation

### 2. JWT Authentication
- Use `JWT-Token` header
- Self-contained tokens with embedded information
- Configurable expiration time
- No database lookup required for validation

## Security Features

1. **Password Hashing**: Passwords are securely hashed (implement proper hashing in production)
2. **Token Expiration**: Both token types have configurable expiration
3. **Rate Limiting**: API calls are rate-limited per teacher
4. **Role-Based Access**: Different permissions based on teacher roles
5. **Device Token Management**: Support for mobile device tokens
6. **Session Management**: Proper session handling and cleanup

## Rate Limiting

- Default: 100 requests per hour per teacher
- Exceeded requests return HTTP 429
- Configurable per endpoint

## Database Requirements

### Required Tables:
- `staff` - Teacher information
- `users_authentication` - Authentication tokens
- `staff_designation` - Teacher designations
- `department` - Departments
- `class_teacher` - Class assignments
- `teacher_subject` - Subject assignments

### Required Fields:
- `staff.app_key` - Mobile device token (add if missing)
- `users_authentication.staff_id` - Link to staff table

## Implementation Notes

1. **Production Security**:
   - Change JWT secret key
   - Implement proper password hashing (bcrypt/Argon2)
   - Use HTTPS for all API calls
   - Implement proper input validation

2. **Performance**:
   - Add database indexes for frequently queried fields
   - Implement caching for frequently accessed data
   - Use connection pooling for database connections

3. **Monitoring**:
   - Log all authentication attempts
   - Monitor failed login attempts
   - Track API usage patterns

## Testing

Use the provided Postman collection for comprehensive API testing. The collection includes:
- Authentication flows
- Profile management
- Error scenarios
- JWT token operations
```
