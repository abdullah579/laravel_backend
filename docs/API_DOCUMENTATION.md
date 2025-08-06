# Laravel Multi-Role Publishing API Documentation

## Table of Contents
1. [Authentication Process](#authentication-process)
2. [API Endpoints](#api-endpoints)
3. [Request/Response Formats](#requestresponse-formats)
4. [Error Codes and Messages](#error-codes-and-messages)
5. [Role-Based Access Matrix](#role-based-access-matrix)

## Authentication Process

### Overview
The API uses Laravel Sanctum for token-based authentication. Users must obtain an access token through login/registration and include it in subsequent requests.

### Authentication Flow
1. **Register/Login** → Receive access token
2. **Include token** in Authorization header for protected routes
3. **Token expires** when user logs out or manually revoked

### Token Usage
```http
Authorization: Bearer {your-access-token}
```

## API Endpoints

### Base URL
```
http://your-domain.com/api
```

### Public Endpoints (No Authentication Required)

#### Register User
```http
POST /register
```
**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```
**Response:** User object with access token (automatically assigned 'author' role)

#### Login User
```http
POST /login
```
**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```
**Response:** User object with roles and access token

### Protected Endpoints (Authentication Required)

#### Authentication Management

##### Logout User
```http
POST /logout
```
**Headers:** `Authorization: Bearer {token}`
**Response:** Success message

##### Get User Profile
```http
GET /profile
```
**Headers:** `Authorization: Bearer {token}`
**Response:** User profile with roles and permissions

### User Management (Admin Only)

#### Get All Users
```http
GET /users
```
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `page` (optional): Page number for pagination
**Response:** Paginated list of users with roles

#### Assign Role to User
```http
POST /users/{id}/assign-role
```
**Headers:** `Authorization: Bearer {token}`
**Request Body:**
```json
{
    "role": "editor"
}
```
**Response:** Updated user object with new role

### Article Management

#### Get Published Articles
```http
GET /articles
```
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `page` (optional): Page number for pagination
**Response:** Paginated list of published articles with author info

#### Get User's Own Articles
```http
GET /articles/mine
```
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `page` (optional): Page number for pagination
**Response:** Paginated list of user's articles (all statuses)

#### Create Article
```http
POST /articles
```
**Headers:** `Authorization: Bearer {token}`
**Request Body:**
```json
{
    "title": "Article Title",
    "content": "Article content here...",
    "status": "draft"
}
```
**Response:** Created article object

#### Update Article
```http
PUT /articles/{id}
```
**Headers:** `Authorization: Bearer {token}`
**Request Body:**
```json
{
    "title": "Updated Title",
    "content": "Updated content...",
    "status": "published"
}
```
**Response:** Updated article object

#### Delete Article
```http
DELETE /articles/{id}
```
**Headers:** `Authorization: Bearer {token}`
**Response:** Success message

#### Publish Article
```http
PATCH /articles/{id}/publish
```
**Headers:** `Authorization: Bearer {token}`
**Response:** Published article object

## Request/Response Formats

### Standard Response Format
All API responses follow this consistent format:

```json
{
    "success": true/false,
    "message": "Response message",
    "data": {}, // Present on success
    "errors": {} // Present on error
}
```

### Success Response Example
```json
{
    "success": true,
    "message": "Article created successfully",
    "data": {
        "id": 1,
        "title": "Sample Article",
        "content": "Article content...",
        "status": "draft",
        "author_id": 1,
        "published_at": null,
        "created_at": "2025-08-06T12:00:00.000000Z",
        "updated_at": "2025-08-06T12:00:00.000000Z",
        "author": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

### Paginated Response Example
```json
{
    "success": true,
    "message": "Published articles retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Article 1",
                "content": "Content...",
                "status": "published",
                "published_at": "2025-08-06T12:00:00.000000Z",
                "author": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            }
        ],
        "first_page_url": "http://domain.com/api/articles?page=1",
        "from": 1,
        "last_page": 2,
        "last_page_url": "http://domain.com/api/articles?page=2",
        "links": [...],
        "next_page_url": "http://domain.com/api/articles?page=2",
        "path": "http://domain.com/api/articles",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 25
    }
}
```

## Error Codes and Messages

### HTTP Status Codes

| Status Code | Description | Usage |
|-------------|-------------|-------|
| 200 | OK | Successful GET, PUT, PATCH requests |
| 201 | Created | Successful POST requests |
| 204 | No Content | Successful DELETE requests |
| 400 | Bad Request | General client errors |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Valid auth but insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server errors |

### Error Response Examples

#### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["The article title is required."],
        "content": ["The article content is required."],
        "role": ["The selected role does not exist."]
    }
}
```

#### Authorization Error (403)
```json
{
    "success": false,
    "message": "You are not authorized to publish articles."
}
```

#### Authentication Error (401)
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

#### Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

#### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error"
}
```

### Common Error Messages

| Error Type | Message | When It Occurs |
|------------|---------|----------------|
| Authentication | "Unauthenticated." | Missing or invalid token |
| Authorization | "Admin access required." | Non-admin accessing admin routes |
| Authorization | "You are not authorized to publish articles." | Author trying to publish |
| Authorization | "You cannot assign roles to yourself." | Admin trying to self-assign |
| Validation | "The article title is required." | Missing required field |
| Validation | "The selected role does not exist." | Invalid role name |
| Business Logic | "Article is already published" | Trying to publish published article |
| Business Logic | "Failed to assign role" | Role assignment failure |

## Role-Based Access Matrix

### User Roles
- **Admin**: Full system access, user management, all article operations
- **Editor**: Article management, can publish any article, cannot manage users
- **Author**: Can create and edit own articles, cannot publish directly

### Permission Matrix

| Action | Admin | Editor | Author | Notes |
|--------|-------|--------|--------|-------|
| **User Management** |
| View all users | ✅ | ❌ | ❌ | Admin only |
| Assign roles | ✅ | ❌ | ❌ | Admin only |
| View own profile | ✅ | ✅ | ✅ | All authenticated users |
| **Article Management** |
| Create article | ✅ | ✅ | ✅ | All can create |
| Edit own article | ✅ | ✅ | ✅ | Own articles only |
| Edit any article | ✅ | ✅ | ❌ | Admin/Editor can edit any |
| Publish article | ✅ | ✅ | ❌ | Admin/Editor only |
| Delete article | ✅ | ❌ | ❌ | Admin only |
| View published articles | ✅ | ✅ | ✅ | All authenticated users |
| View own articles | ✅ | ✅ | ✅ | All can view their own |
| View draft articles (others) | ✅ | ✅ | ❌ | Admin/Editor only |

### Detailed Permission Rules

#### Article Creation
- **All roles** can create articles
- **Authors** can only create drafts
- **Editors/Admins** can create and publish directly

#### Article Editing
- **Authors** can edit their own articles only
- **Editors** can edit any article
- **Admins** can edit any article

#### Article Publishing
- **Authors** cannot publish (articles remain as drafts)
- **Editors** can publish any article
- **Admins** can publish any article

#### Article Deletion
- **Only Admins** can delete articles
- **Editors/Authors** cannot delete articles

#### User Management
- **Only Admins** can view all users
- **Only Admins** can assign/remove roles
- **Admins cannot assign roles to themselves** (security measure)

### API Endpoint Access by Role

| Endpoint | Admin | Editor | Author | Guest |
|----------|-------|--------|--------|-------|
| `POST /register` | ✅ | ✅ | ✅ | ✅ |
| `POST /login` | ✅ | ✅ | ✅ | ✅ |
| `POST /logout` | ✅ | ✅ | ✅ | ❌ |
| `GET /profile` | ✅ | ✅ | ✅ | ❌ |
| `GET /users` | ✅ | ❌ | ❌ | ❌ |
| `POST /users/{id}/assign-role` | ✅ | ❌ | ❌ | ❌ |
| `GET /articles` | ✅ | ✅ | ✅ | ❌ |
| `GET /articles/mine` | ✅ | ✅ | ✅ | ❌ |
| `POST /articles` | ✅ | ✅ | ✅ | ❌ |
| `PUT /articles/{id}` | ✅* | ✅* | ✅** | ❌ |
| `DELETE /articles/{id}` | ✅ | ❌ | ❌ | ❌ |
| `PATCH /articles/{id}/publish` | ✅ | ✅ | ❌ | ❌ |

**Legend:**
- ✅ = Full access
- ❌ = No access
- ✅* = Can edit any article
- ✅** = Can edit own articles only

## Testing the API

### Using cURL

#### Register a new user
```bash
curl -X POST http://your-domain.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Login
```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Create an article
```bash
curl -X POST http://your-domain.com/api/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "title": "My First Article",
    "content": "This is the content of my first article.",
    "status": "draft"
  }'
```

#### Get published articles
```bash
curl -X GET http://your-domain.com/api/articles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Rate Limiting
- No rate limiting implemented by default
- Consider implementing rate limiting for production use

### Security Considerations
- Always use HTTPS in production
- Store tokens securely on client side
- Implement token refresh mechanism for long-lived applications
- Consider implementing API versioning for future updates

---

**Last Updated:** August 6, 2025
**API Version:** 1.0
**Framework:** Laravel 11 with Sanctum Authentication
