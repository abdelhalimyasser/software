# NextHire API Documentation

> **Version:** 1.0  
> **Base URL:** `http://localhost:8000/api`  
> **Content-Type:** `application/json` (unless uploading files → `multipart/form-data`)  
> **Authentication:** Bearer Token (Laravel Sanctum)  
> **Author:** Abdelhalim Yasser  
> **Last Updated:** 29 April 2026

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Error Handling](#error-handling)
4. [User Roles](#user-roles)
5. [API Endpoints](#api-endpoints)
   - [Register Candidate](#1-register-candidate)
   - [Login](#2-login)
   - [Logout](#3-logout)
   - [Update Profile](#4-update-profile)
   - [Forget Password](#5-forget-password)
   - [Reset Password](#6-reset-password)
   - [Register Employee](#7-register-employee)
   - [Update Employee](#8-update-employee)
6. [Endpoint Summary](#endpoint-summary)
7. [Data Models](#data-models)
8. [File Upload Specifications](#file-upload-specifications)
9. [Password Policy](#password-policy)

---

## Overview

NextHire is an AI-Driven Smart Recruitment & Interview Management System. This API handles user authentication, registration, profile management, and password recovery for both **Candidates** (public self-registration) and **Employees** (admin-managed registration).

---

## Authentication

The API uses **Laravel Sanctum** token-based authentication.

| Header | Value | Required For |
|--------|-------|--------------|
| `Authorization` | `Bearer {token}` | All **private** endpoints |
| `Accept` | `application/json` | All endpoints |
| `Content-Type` | `application/json` | JSON payloads |
| `Content-Type` | `multipart/form-data` | File upload payloads |

Tokens are returned by the [Login](#2-login) and registration endpoints. Include the token in the `Authorization` header for all authenticated requests.

---

## Error Handling

All error responses follow a consistent structure:

```json
{
    "error": "Human-readable error message."
}
```

### Standard HTTP Status Codes

| Code | Meaning | When |
|------|---------|------|
| `200` | OK | Successful read/update |
| `201` | Created | Successful registration |
| `400` | Bad Request | Invalid token (password reset) |
| `401` | Unauthorized | Invalid credentials or missing token |
| `403` | Forbidden | Insufficient role permissions |
| `404` | Not Found | Resource does not exist |
| `422` | Unprocessable Entity | Validation errors |
| `500` | Internal Server Error | Server-side failure (file upload, DB error) |

### Validation Error Response (422)

```json
{
    "message": "The first name field is required. (and 3 more errors)",
    "errors": {
        "first_name": ["Please enter your first name."],
        "email": ["Please enter a valid email address."],
        "password": ["The password must be at least 8 characters long."],
        "profile_picture": ["Please upload a profile picture."]
    }
}
```

---

## User Roles

| Role | Value | Description |
|------|-------|-------------|
| Candidate | `CANDIDATE` | Job applicant — self-registers via public endpoint |
| Employee | `EMPLOYEE` | Company employee — registered by admin |
| HR Admin | `HR_ADMIN` | Human resources administrator |
| Interviewer | `INTERVIEWER` | Conducts candidate interviews |
| Shadow Interviewer | `SHADOW_INTERVIEWER` | Observes interviews for training |
| Department Manager | `DEPARTMENT_MANAGER` | Approves job requisitions |

---

## API Endpoints

---

### 1. Register Candidate

Creates a new candidate account with file uploads. A verification email is dispatched and an auth token is returned.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/public/auth/register` |
| **Method** | `POST` |
| **Auth** | ❌ None (public) |
| **Content-Type** | `multipart/form-data` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `first_name` | `string` | ✅ | max: 255 | Candidate's first name |
| `last_name` | `string` | ✅ | max: 255 | Candidate's last name |
| `birth_date` | `date` | ✅ | Must be ≥ 18 years old | Date of birth (`YYYY-MM-DD`) |
| `email` | `string` | ✅ | valid email, unique | Email address |
| `phone_number` | `string` | ✅ | max: 15, unique | Phone number |
| `password` | `string` | ✅ | min: 8, mixed case, numbers, symbols, uncompromised | Account password |
| `skills` | `array` | ❌ | array of strings | e.g. `["PHP", "Laravel"]` |
| `experience_years` | `integer` | ✅ | 0–50 | Years of professional experience |
| `profile_picture` | `file` | ✅ | image, jpeg/png/jpg/webp, max: 2 MB | Profile photo |
| `resume` | `file` | ✅ | pdf/doc/docx, max: 5 MB | Resume document |
| `docs` | `file` | ✅ | pdf/png/jpg/zip, max: 10 MB | Supporting documents |

#### Success Response — `201 Created`

```json
{
    "message": "Candidate registered successfully",
    "user": {
        "id": 1,
        "first_name": "Jane",
        "last_name": "Doe",
        "name": "Jane Doe",
        "birth_date": "2000-01-01",
        "email": "jane@example.com",
        "phone_number": "0100000000",
        "role": "CANDIDATE",
        "profile_picture_path": "profiles/abc123.png",
        "resume_path": "resumes/def456.pdf",
        "docs_path": "documents/ghi789.zip",
        "skills": ["PHP", "Laravel"],
        "experience_years": 4,
        "created_at": "2026-04-29T18:00:00.000000Z",
        "updated_at": "2026-04-29T18:00:00.000000Z"
    },
    "token": "1|abc123def456..."
}
```

#### Error Responses

| Code | Scenario | Example |
|------|----------|---------|
| `422` | Validation failure | Missing required fields, underage, duplicate email |
| `500` | File upload failure | Disk full, permissions error |
| `500` | Registration failure | Database error |

---

### 2. Login

Authenticates a user and returns an access token. Available on two paths for flexibility.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/auth/login` **or** `/api/v1/private/auth/login` |
| **Method** | `POST` |
| **Auth** | ❌ None (public) |
| **Content-Type** | `application/json` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `email` | `string` | ✅ | valid email, must exist in `users` table | Registered email |
| `password` | `string` | ✅ | min: 8, mixed case, numbers, symbols | Account password |

#### Success Response — `200 OK`

```json
{
    "user": {
        "id": 1,
        "first_name": "Jane",
        "last_name": "Doe",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "role": "CANDIDATE",
        "phone_number": "0100000000",
        "created_at": "2026-04-29T18:00:00.000000Z",
        "updated_at": "2026-04-29T18:00:00.000000Z"
    },
    "token": "2|xyz789abc012..."
}
```

#### Error Responses

| Code | Scenario | Body |
|------|----------|------|
| `422` | Email not found / weak password | `{ "errors": { "email": ["..."] } }` |
| `401` | Wrong password | `{ "error": "The provided credentials are incorrect." }` |

---

### 3. Logout

Revokes the current access token.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/logout` |
| **Method** | `POST` |
| **Auth** | ✅ Bearer Token |
| **Content-Type** | `application/json` |

#### Request Body

*None*

#### Success Response — `200 OK`

```json
{
    "message": "Successfully logged out"
}
```

#### Error Responses

| Code | Scenario |
|------|----------|
| `401` | Missing or invalid token |

---

### 4. Update Profile

Updates the authenticated user's profile. All fields are optional — only send what needs changing.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/profile/update` |
| **Method** | `POST` |
| **Auth** | ✅ Bearer Token |
| **Content-Type** | `multipart/form-data` (if uploading files) or `application/json` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `first_name` | `string` | ❌ | max: 255 | Updated first name |
| `last_name` | `string` | ❌ | max: 255 | Updated last name |
| `phone_number` | `string` | ❌ | max: 15, unique (excl. self) | Updated phone number |
| `skills` | `array` | ❌ | array of strings | Updated skills list |
| `experience_years` | `integer` | ❌ | 0–50 | Updated experience years |
| `profile_picture` | `file` | ❌ | image, jpeg/png/jpg/webp, max: 2 MB | New profile photo |
| `resume` | `file` | ❌ | pdf/doc/docx, max: 5 MB | New resume |
| `docs` | `file` | ❌ | pdf/png/jpg/zip, max: 10 MB | New documents |

#### Success Response — `200 OK`

```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "first_name": "Jane",
        "last_name": "Updated",
        "name": "Jane Updated",
        "email": "jane@example.com",
        "phone_number": "0100000005",
        "skills": ["PHP", "Testing"],
        "experience_years": 5,
        "profile_picture_path": "profiles/new_photo.png",
        "updated_at": "2026-04-29T19:00:00.000000Z"
    }
}
```

#### Error Responses

| Code | Scenario |
|------|----------|
| `401` | Not authenticated |
| `422` | Validation failure (duplicate phone, invalid file type) |
| `500` | File upload or database error |

---

### 5. Forget Password

Sends a password reset link to the user's email address.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/auth/forget-password` |
| **Method** | `POST` |
| **Auth** | ❌ None (public) |
| **Content-Type** | `application/json` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `email` | `string` | ✅ | valid email, must exist in `users` table | Registered email |

#### Success Response — `200 OK`

```json
{
    "message": "Reset link sent to your email."
}
```

#### Error Responses

| Code | Scenario | Body |
|------|----------|------|
| `422` | Email not registered | `{ "errors": { "email": ["..."] } }` |
| `500` | Mail delivery failure | `{ "error": "Unable to send reset link." }` |

---

### 6. Reset Password

Resets the user's password using the token from the reset email.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/auth/reset-password` |
| **Method** | `POST` |
| **Auth** | ❌ None (public) |
| **Content-Type** | `application/json` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `email` | `string` | ✅ | valid email, must exist in `users` table | Account email |
| `token` | `string` | ✅ | — | Reset token from email |
| `password` | `string` | ✅ | min: 8, mixed case, numbers, symbols | New password |
| `password_confirmation` | `string` | ✅ | must match `password` | Password confirmation |

#### Success Response — `200 OK`

```json
{
    "message": "Password has been successfully reset."
}
```

#### Error Responses

| Code | Scenario | Body |
|------|----------|------|
| `422` | Invalid email or weak password | `{ "errors": { ... } }` |
| `400` | Invalid or expired token | `{ "error": "Invalid token or email." }` |

---

### 7. Register Employee

Creates a new employee account. The system auto-generates a unique `emp_id` in the format `NH-EMP-YYYY-XXXX`.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/private/auth/register-new-employee` |
| **Method** | `POST` |
| **Auth** | ❌ None (should be admin-restricted in production) |
| **Content-Type** | `multipart/form-data` (if uploading) or `application/json` |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `first_name` | `string` | ✅ | max: 255 | Employee's first name |
| `last_name` | `string` | ✅ | max: 255 | Employee's last name |
| `email` | `string` | ✅ | valid email, unique | Employee email |
| `password` | `string` | ✅ | min: 8 | Account password |
| `role` | `string` | ✅ | see [User Roles](#user-roles) | One of: `EMPLOYEE`, `HR_ADMIN`, `INTERVIEWER`, `SHADOW_INTERVIEWER`, `DEPARTMENT_MANAGER` |
| `phone_number` | `string` | ❌ | max: 15, unique | Phone number |
| `profile_picture` | `file` | ❌ | image, jpeg/png/jpg/webp, max: 2 MB | Profile photo |

#### Success Response — `201 Created`

```json
{
    "message": "Employee registered successfully",
    "employee": {
        "id": 2,
        "first_name": "John",
        "last_name": "Smith",
        "name": "John Smith",
        "email": "john@example.com",
        "role": "EMPLOYEE",
        "emp_id": "NH-EMP-2026-4271",
        "phone_number": "0100000001",
        "profile_picture_path": null,
        "created_at": "2026-04-29T18:30:00.000000Z",
        "updated_at": "2026-04-29T18:30:00.000000Z"
    },
    "id": 2,
    "token": "3|mno345pqr678..."
}
```

#### Error Responses

| Code | Scenario |
|------|----------|
| `422` | Validation failure (missing fields, duplicate email) |
| `500` | File upload or registration failure |

---

### 8. Update Employee

Updates an existing employee's data by ID. All fields are optional.

| Property | Value |
|----------|-------|
| **URL** | `/api/v1/private/auth/update-employee/{id}` |
| **Method** | `PUT` |
| **Auth** | ❌ None (should be admin-restricted in production) |
| **Content-Type** | `multipart/form-data` (if uploading) or `application/json` |

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | `integer` | Employee's database ID |

#### Request Body

| Field | Type | Required | Constraints | Description |
|-------|------|----------|-------------|-------------|
| `first_name` | `string` | ❌ | max: 255 | Updated first name |
| `last_name` | `string` | ❌ | max: 255 | Updated last name |
| `email` | `string` | ❌ | valid email, unique (excl. self) | Updated email |
| `password` | `string` | ❌ | min: 8 | New password |
| `role` | `string` | ❌ | see [User Roles](#user-roles) | Updated role |
| `emp_id` | `string` | ❌ | unique (excl. self) | Updated employee ID |
| `phone_number` | `string` | ❌ | max: 15, unique (excl. self) | Updated phone |
| `profile_picture` | `file` | ❌ | image, jpeg/png/jpg/webp, max: 2 MB | New profile photo |

#### Success Response — `200 OK`

```json
{
    "message": "Employee updated successfully",
    "employee": {
        "id": 2,
        "first_name": "John",
        "last_name": "Updated",
        "name": "John Updated",
        "email": "john@example.com",
        "role": "HR_ADMIN",
        "emp_id": "NH-EMP-2026-4271",
        "phone_number": "0100000099",
        "updated_at": "2026-04-29T19:30:00.000000Z"
    }
}
```

#### Error Responses

| Code | Scenario | Body |
|------|----------|------|
| `404` | Employee not found | `{ "error": "Employee not found." }` |
| `422` | Validation failure | `{ "errors": { ... } }` |
| `500` | File upload or update failure | `{ "error": "Failed to update employee: ..." }` |

---

## Endpoint Summary

| # | Method | URL | Auth | Description |
|---|--------|-----|------|-------------|
| 1 | `POST` | `/api/v1/public/auth/register` | ❌ | Register candidate (with files) |
| 2 | `POST` | `/api/v1/auth/login` | ❌ | Login (all users) |
| 2b | `POST` | `/api/v1/private/auth/login` | ❌ | Login (employee alias) |
| 3 | `POST` | `/api/v1/logout` | ✅ | Logout (revoke token) |
| 4 | `POST` | `/api/v1/profile/update` | ✅ | Update own profile |
| 5 | `POST` | `/api/v1/auth/forget-password` | ❌ | Request password reset |
| 6 | `POST` | `/api/v1/auth/reset-password` | ❌ | Reset password with token |
| 7 | `POST` | `/api/v1/private/auth/register-new-employee` | ❌ | Register new employee |
| 8 | `PUT` | `/api/v1/private/auth/update-employee/{id}` | ❌ | Update employee by ID |

---

## Data Models

### User Object

```json
{
    "id": "integer — auto-increment primary key",
    "name": "string — auto-computed from first_name + last_name",
    "first_name": "string",
    "last_name": "string",
    "birth_date": "date (YYYY-MM-DD) | null",
    "email": "string — unique",
    "phone_number": "string (max 15) — unique | null",
    "role": "string — CANDIDATE | EMPLOYEE | HR_ADMIN | INTERVIEWER | SHADOW_INTERVIEWER | DEPARTMENT_MANAGER",
    "emp_id": "string — auto-generated for employees (NH-EMP-YYYY-XXXX) | null",
    "profile_picture_path": "string — storage path | null",
    "resume_path": "string — storage path | null",
    "docs_path": "string — storage path | null",
    "skills": "array of strings | null",
    "experience_years": "integer (0-50) | null",
    "email_verified_at": "datetime | null",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Employee ID Format

```
NH-EMP-{YEAR}-{4-DIGIT RANDOM}
Example: NH-EMP-2026-4271
```

Generated automatically on first save. Guaranteed unique via DB check loop.

---

## File Upload Specifications

| Field | Storage Disk | Directory | Allowed Types | Max Size |
|-------|-------------|-----------|---------------|----------|
| `profile_picture` | `public` | `profiles/` | jpeg, png, jpg, webp | 2 MB |
| `resume` | `local` | `resumes/` | pdf, doc, docx | 5 MB |
| `docs` | `local` | `documents/` | pdf, png, jpg, zip | 10 MB |

> **Note:** Files on the `public` disk are accessible via URL. Files on the `local` disk are private and require a download endpoint (not yet implemented).

---

## Password Policy

| Rule | Register Candidate | Login | Register Employee | Reset Password |
|------|-------------------|-------|-------------------|----------------|
| Minimum length | 8 | 8 | 8 | 8 |
| Mixed case (upper + lower) | ✅ | ✅ | ❌ | ✅ |
| Contains numbers | ✅ | ✅ | ❌ | ✅ |
| Contains symbols | ✅ | ✅ | ❌ | ✅ |
| Not compromised (HIBP) | ✅ | ❌ | ❌ | ❌ |
| Confirmation required | ❌ | ❌ | ❌ | ✅ |
