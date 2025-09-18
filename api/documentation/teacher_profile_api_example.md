
## staff profile api

curl -X GET "http://localhost/amt/api/teacher/profile/6" \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@"




POST "http://localhost/amt/api/teacher/profile" \
  -H "Content-Type: application/json" \
  -H "Client-Service: smartschool" \
  -H "Auth-Key: schoolAdmin@"
  -d '{"staff_id": 6}'




response:-

{
  "status": 1,
  "message": "Profile retrieved successfully.",
  "staff_id": "6",
  "basic_info": {
    "id": "6",
    "employee_id": "200226",
    "name": "MAHA LAKSHMI",
    "surname": "SALLA",
    "full_name": "MAHA LAKSHMI SALLA",
    "designation": "2",
    "designation_name": "Accountant",
    "department": "9",
    "department_name": "Finance",
    "user_type": "",
    "role_id": "",
    "is_active": "1",
    "date_of_joining": "2023-08-01",
    "date_of_leaving": null,
    "disable_at": null
  },
  "contact_info": {
    "email": "mahalakshmisalla70@gmail.com",
    "contact_no": "8328595488",
    "emergency_contact_no": "6303727148"
  },
  "personal_info": {
    "gender": "Female",
    "dob": "2002-11-26",
    "marital_status": "Single",
    "father_name": "Salla Vijay chandhra",
    "mother_name": "Salla Parameshwari",
    "qualification": "B.sc computer science",
    "work_exp": "1 year",
    "note": ""
  },
  "address_info": {
    "local_address": "Bc colony ,venkatagiri,tirupati-524404",
    "permanent_address": "Bc colony ,venkatagiri,tirupati-524404"
  },
  "bank_details": {
    "account_title": "",
    "bank_name": "",
    "bank_branch": "",
    "bank_account_no": "",
    "ifsc_code": "",
    "payscale": "",
    "basic_salary": "0",
    "epf_no": "",
    "contract_type": "",
    "shift": "",
    "location": ""
  },
  "social_media": {
    "facebook": "",
    "twitter": "",
    "linkedin": "",
    "instagram": ""
  },
  "documents": [],
  "custom_fields": [],
  "qr_code": {
    "data": {
      "type": "staff_profile",
      "staff_id": "6",
      "employee_id": "200226",
      "name": "MAHA LAKSHMI SALLA",
      "designation": "Accountant",
      "department": "Finance",
      "email": "mahalakshmisalla70@gmail.com",
      "contact": "8328595488",
      "profile_url": "https://school.cyberdetox.in/api/api/teacher/profile/6"
    },
    "qr_string": "{\"type\":\"staff_profile\",\"staff_id\":\"6\",\"employee_id\":\"200226\",\"name\":\"MAHA LAKSHMI SALLA\",\"designation\":\"Accountant\",\"department\":\"Finance\",\"email\":\"mahalakshmisalla70@gmail.com\",\"contact\":\"8328595488\",\"profile_url\":\"https:\\/\\/school.cyberdetox.in\\/api\\/api\\/teacher\\/profile\\/6\"}",
    "qr_code_url": "https://school.cyberdetox.in/api/api/teacher/qr-code/6"
  },
  "profile_image": "https://school.cyberdetox.in/api/uploads/staff_images/1716194826-1802404949664b0e0aa5de2!WhatsApp Image 2024-05-20 at 2.16.50 PM.jpeg",
  "school_settings": {
    "staff_phone": 1,
    "staff_emergency_contact": 1,
    "staff_marital_status": 1,
    "staff_father_name": 1,
    "staff_mother_name": 1,
    "staff_qualification": 1,
    "staff_work_experience": 1,
    "staff_note": 1,
    "staff_current_address": 1,
    "staff_permanent_address": 1,
    "staff_account_details": 1,
    "staff_social_media": 1,
    "staff_upload_documents": 1,
    "staff_barcode": 1
  }
}
