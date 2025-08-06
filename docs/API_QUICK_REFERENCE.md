# Laravel Multi-Role Publishing API - Quick Reference

## Base URL
```
http://your-domain.com/api
```

## Authentication
All protected endpoints require:
```
Authorization: Bearer {your-access-token}
```

## Quick Endpoint Reference

### 🔓 Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | Register new user (auto-assigned 'author' role) |
| POST | `/login` | Login user and get access token |

### 🔒 Protected Endpoints

#### User Management (Admin Only)
| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| GET | `/users` | Get all users | Admin |
| POST | `/users/{id}/assign-role` | Assign role to user | Admin |

#### Profile Management
| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| GET | `/profile` | Get current user profile | Any |
| POST | `/logout` | Logout current user | Any |

#### Article Management
| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| GET | `/articles` | Get published articles | Any |
| GET | `/articles/mine` | Get user's own articles | Any |
| POST | `/articles` | Create new article | Any |
| PUT | `/articles/{id}` | Update article | Owner/Editor/Admin |
| DELETE | `/articles/{id}` | Delete article | Admin |
| PATCH | `/articles/{id}/publish` | Publish article | Editor/Admin |

## Role Permissions Summary

### 👑 Admin
- ✅ All user management
- ✅ All article operations
- ✅ Can delete any article
- ✅ Can assign roles (except to self)

### ✏️ Editor
- ❌ User management
- ✅ Create/edit/publish any article
- ❌ Delete articles
- ❌ Assign roles

### 📝 Author
- ❌ User management
- ✅ Create articles (drafts only)
- ✅ Edit own articles
- ❌ Publish articles
- ❌ Delete articles
- ❌ Assign roles

## Sample Requests

### Register User
```json
POST /register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

### Login
```json
POST /login
{
    "email": "john@example.com",
    "password": "password123"
}
```

### Create Article
```json
POST /articles
Authorization: Bearer {token}
{
    "title": "My Article",
    "content": "Article content here...",
    "status": "draft"
}
```

### Assign Role (Admin Only)
```json
POST /users/2/assign-role
Authorization: Bearer {admin-token}
{
    "role": "editor"
}
```

## Response Format
```json
{
    "success": true/false,
    "message": "Response message",
    "data": {}, // on success
    "errors": {} // on error
}
```

## Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `422` - Validation Error
- `404` - Not Found

## Test Users (After Seeding)
```
Admin: admin@example.com / password123
Editor: editor@example.com / password123
Author: author@example.com / password123
Super User: super@example.com / password123 (Admin + Editor)
```

## Validation Rules

### Article Creation/Update
- `title`: required (create), optional (update), max 255 chars
- `content`: required (create), optional (update)
- `status`: optional, must be 'draft' or 'published'

### Role Assignment
- `role`: required, must exist in roles table ('admin', 'editor', 'author')

### User Registration
- `name`: required, max 255 chars
- `email`: required, valid email, unique
- `password`: required, min 8 chars

## Error Examples

### Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["The article title is required."]
    }
}
```

### Authorization Error
```json
{
    "success": false,
    "message": "You are not authorized to publish articles."
}
```

## Development Commands

### Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

### Create Test Data
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```
