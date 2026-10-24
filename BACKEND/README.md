# LA Solution Partnership - Backend API & Postman Testing Guide

Streamlined, lightweight PHP & MySQL backend focusing on **Inquiries (CRM Leads)** and **Authentication (Admin Security)**, with ecosystem verticals configured as constants.

---

## 1. System Architecture

```
BACKEND/
├── config/
│   ├── config.php          # Database credentials, CORS headers, timezone (Asia/Kolkata)
│   └── database.php        # PDO Singleton connection manager with UTF-8
├── database/
│   ├── schema.sql          # DDL for inquiries and admin_users tables
│   ├── seed.sql            # Superadmin account (admin / Admin@12345) and baseline leads
│   ├── migrate.php         # Migration runner (CLI & Browser)
│   └── test_runner.php     # Automated test suite
├── src/
│   ├── Utils/
│   │   ├── Response.php    # Standardized JSON response handler (200, 201, 400, 401, 422, 500)
│   │   ├── Validator.php   # Input sanitization and validation rules
│   │   └── Auth.php        # Bearer token and session authentication guards
│   └── Models/
│       ├── Inquiry.php     # Inquiries CRUD, lead code generation (LEAD-101), search, filters
│       ├── AdminUser.php   # Admin credentials, Bcrypt verification, and token sessions
│       └── Vertical.php    # Hardcoded 8 ecosystem verticals constants
├── api/
│   ├── health.php          # System health check (DB connectivity & row counts)
│   ├── auth/
│   │   ├── login.php       # POST: Admin login (Returns Bearer token)
│   │   ├── logout.php      # POST: Invalidate token and session
│   │   └── me.php          # GET: Current authenticated admin profile
│   ├── inquiries/
│   │   ├── index.php       # GET: List/search/filter leads | POST: Create inquiry
│   │   ├── detail.php      # GET: Single lead | PUT: Update lead | DELETE: Remove lead
│   │   ├── status.php      # POST: Quick status update (New, Contacted, In Discussion, etc.)
│   │   └── export.php      # GET: Streams clean CSV export with UTF-8 BOM
│   └── dashboard/
│       └── stats.php       # GET: Overview KPIs, Chart.js trends, vertical shares
├── router.php              # Built-in PHP server router (handles both /api and /BACKEND/api)
├── .htaccess               # Apache CORS, security headers, directory privacy
├── index.php               # Interactive API documentation and status dashboard
└── README.md               # Architecture documentation & Postman testing guide
```

---

## 2. Database Schema

The database `la_solution_db` maintains only two core tables:

### Table 1: `inquiries`
- `id` (BIGINT UNSIGNED, Primary Key, Auto Increment)
- `lead_code` (VARCHAR(25), Unique) - e.g. `LEAD-101`, `LEAD-102`
- `name` (VARCHAR(150), Not Null) - Submitter name
- `phone` (VARCHAR(35), Not Null) - Phone / WhatsApp number
- `email` (VARCHAR(150), Not Null) - Validated email address
- `interest_type` (VARCHAR(120), Not Null) - e.g. `Franchise / Territory Partner`
- `vertical` (VARCHAR(180), Not Null) - e.g. `FitnessGuru.org.in - Gym Management`
- `location` (VARCHAR(180), Default `'Not specified'`) - City & State
- `message` (TEXT, Nullable) - Business requirement
- `status` (ENUM: `'New'`, `'Contacted'`, `'In Discussion'`, `'MOU Signed'`, `'Closed'`, `'Archived'`) - Default `'New'`
- `priority` (ENUM: `'Low'`, `'Medium'`, `'High'`, `'Urgent'`) - Default `'Medium'`
- `notes` (TEXT, Nullable) - Internal follow-up notes
- `ip_address` (VARCHAR(45), Nullable)
- `user_agent` (VARCHAR(255), Nullable)
- `created_at` (DATETIME, Default CURRENT_TIMESTAMP)
- `updated_at` (DATETIME, On Update CURRENT_TIMESTAMP)

### Table 2: `admin_users`
- `id` (INT UNSIGNED, Primary Key, Auto Increment)
- `username` (VARCHAR(60), Unique) - e.g. `admin`
- `email` (VARCHAR(120), Unique) - e.g. `admin@lasolution.in`
- `password_hash` (VARCHAR(255), Not Null) - Bcrypt hashed password
- `full_name` (VARCHAR(120), Not Null)
- `role` (ENUM: `'superadmin'`, `'admin'`, `'viewer'`) - Default `'admin'`
- `status` (ENUM: `'active'`, `'inactive'`) - Default `'active'`
- `auth_token` (VARCHAR(64), Nullable) - Bearer access token
- `token_expires_at` (DATETIME, Nullable)
- `last_login_at` (DATETIME, Nullable)

---

## 3. How to Start the Server Locally

### Option A: From Project Root (Recommended)
This hosts the **Landing Page**, **Admin Panel**, and **Backend** all on the same server:
```powershell
cd D:\CODES\PROJECTS\LA-SOLUTION
& "C:\xampp\php\php.exe" -S localhost:8000
```
- **Landing Page**: [http://localhost:8000/LANDING-PAGE/index.html](http://localhost:8000/LANDING-PAGE/index.html)
- **Admin Dashboard**: [http://localhost:8000/ADMIN-PANEL/admindashboard.html](http://localhost:8000/ADMIN-PANEL/admindashboard.html)
- **API Portal**: [http://localhost:8000/BACKEND/index.php](http://localhost:8000/BACKEND/index.php)

---

### Option B: From Inside `backend` Folder
If you prefer running directly inside the `backend` directory, launch with `router.php`:
```powershell
cd D:\CODES\PROJECTS\LA-SOLUTION\backend
& "C:\xampp\php\php.exe" -S localhost:8000 router.php
```
> `router.php` automatically routes requests whether prefixed with `/BACKEND/` or not!

---

## 4. Testing APIs in Postman (Step-by-Step)

Set your base URL in Postman:
- If running from **Project Root**: `http://localhost:8000/BACKEND/api`
- If running from **`backend` folder with router.php**: `http://localhost:8000/api` (or `http://localhost:8000/BACKEND/api`)

Below examples use `http://localhost:8000/api`:

---

### Step 1: Health Check (Verify Server & Database)
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/health.php`
- **Headers**: None
- **Expected Status**: `200 OK`
- **Response**:
```json
{
  "success": true,
  "message": "LA Solution Backend API is healthy and operational.",
  "data": {
    "status": "UP",
    "app_name": "LA Solution Partnership Backend API",
    "version": "1.0.0",
    "database": {
      "connected": true,
      "tables": {
        "inquiries": 9,
        "admin_users": 1
      }
    }
  }
}
```

---

### Step 2: Admin Login (Get Bearer Token)
- **Method**: `POST`
- **URL**: `http://localhost:8000/api/auth/login.php`
- **Headers**:
  - `Content-Type: application/json`
- **Body (raw JSON)**:
```json
{
  "username": "admin",
  "password": "Admin@12345"
}
```
- **Expected Status**: `200 OK`
- **Response**:
```json
{
  "success": true,
  "message": "Welcome back, Executive Administrator!",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@lasolution.in",
    "full_name": "Executive Administrator",
    "role": "superadmin",
    "token": "2269d6e56e20d5c7...",
    "token_expires_at": "2026-10-27 21:35:46"
  }
}
```
> **Tip for Postman**: Copy the `token` value from the response to use in authenticated requests.

---

### Step 3: Check Current Profile
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/auth/me.php`
- **Headers**:
  - `Authorization: Bearer <your_token_here>`
- **Expected Status**: `200 OK`
- **Response**: Returns your admin user profile.

---

### Step 4: Submit a New Partnership Inquiry (Lead Capture)
Used by the Landing Page form and the Admin "Add Lead" modal.
- **Method**: `POST`
- **URL**: `http://localhost:8000/api/inquiries/index.php`
- **Headers**:
  - `Content-Type: application/json`
- **Body (raw JSON)**:
```json
{
  "fullName": "Sanjay Pradhan",
  "phoneNumber": "+91 94370 12345",
  "emailAddress": "sanjay@odishafit.in",
  "partnershipType": "Franchise / Territory Partner",
  "verticalInterest": "FitnessGuru.org.in - Gym Management",
  "cityState": "Bhubaneswar, Odisha",
  "message": "Interested in opening 2 gym centers with QR check-in technology."
}
```
- **Expected Status**: `201 Created`
- **Response**:
```json
{
  "success": true,
  "message": "Thank you! Your partnership inquiry has been logged successfully.",
  "lead_code": "LEAD-108",
  "data": {
    "id": 103,
    "lead_code": "LEAD-108",
    "name": "Sanjay Pradhan",
    "phone": "+91 94370 12345",
    "email": "sanjay@odishafit.in",
    "interest_type": "Franchise / Territory Partner",
    "vertical": "FitnessGuru.org.in - Gym Management",
    "location": "Bhubaneswar, Odisha",
    "message": "Interested in opening 2 gym centers with QR check-in technology.",
    "status": "New",
    "priority": "Medium",
    "created_at": "2026-10-24 21:38:00"
  }
}
```

---

### Step 5: List Inquiries with Search & Filtering
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/inquiries/index.php?page=1&limit=5&status=New`
- **Optional Query Parameters**:
  - `search`: Filter by name, phone, email, city, notes (e.g. `?search=Sanjay`)
  - `status`: Filter by status (`New`, `Contacted`, `In Discussion`, `MOU Signed`, `Archived`, or `ALL`)
  - `vertical`: Filter by vertical keyword (e.g. `?vertical=FitnessGuru`)
  - `type`: Filter by interest type (e.g. `?type=Franchise`)
  - `page`: Page number (default: 1)
  - `limit`: Records per page (default: 8)
- **Expected Status**: `200 OK`
- **Response**: Returns array of leads with `pagination` metadata (`total`, `page`, `per_page`, `total_pages`).

---

### Step 6: Get Single Lead Details
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/inquiries/detail.php?id=LEAD-101`
- **Expected Status**: `200 OK`
- **Response**:
```json
{
  "success": true,
  "message": "Inquiry retrieved.",
  "data": {
    "id": 1,
    "lead_code": "LEAD-101",
    "name": "Rajesh Mohanty",
    "phone": "+91 98610 22334",
    "email": "rajesh.mohanty@apexfit.in",
    "status": "New",
    "priority": "High"
  }
}
```

---

### Step 7: Update Lead Information
- **Method**: `PUT` (or `POST`)
- **URL**: `http://localhost:8000/api/inquiries/detail.php?id=LEAD-101`
- **Headers**:
  - `Content-Type: application/json`
- **Body (raw JSON)**:
```json
{
  "name": "Rajesh Mohanty (Updated)",
  "status": "In Discussion",
  "priority": "Urgent",
  "notes": "Discussed franchise deposit terms over call."
}
```
- **Expected Status**: `200 OK`
- **Response**: Returns the updated lead record.

---

### Step 8: Quick Status Update
- **Method**: `POST`
- **URL**: `http://localhost:8000/api/inquiries/status.php`
- **Headers**:
  - `Content-Type: application/json`
- **Body (raw JSON)**:
```json
{
  "id": "LEAD-101",
  "status": "MOU Signed",
  "notes": "Agreement signed at state conclave."
}
```
- **Expected Status**: `200 OK`
- **Response**:
```json
{
  "success": true,
  "message": "Status for inquiry [LEAD-101] updated to 'MOU Signed'."
}
```

---

### Step 9: Delete an Inquiry
- **Method**: `DELETE`
- **URL**: `http://localhost:8000/api/inquiries/detail.php?id=LEAD-108`
- **Expected Status**: `200 OK`
- **Response**:
```json
{
  "success": true,
  "message": "Inquiry [LEAD-108] permanently removed."
}
```

---

### Step 10: Dashboard Analytics & KPIs
Used by the Executive Admin Dashboard Overview tab.
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/dashboard/stats.php`
- **Expected Status**: `200 OK`
- **Response**: Returns total leads count, conversion rate %, status breakdown, monthly trend chart points, and hardcoded vertical list.

---

### Step 11: Export Leads to CSV
- **Method**: `GET`
- **URL**: `http://localhost:8000/api/inquiries/export.php`
- **Expected Status**: `200 OK`
- **Response**: Direct CSV file stream with UTF-8 BOM (`Content-Type: text/csv`).
