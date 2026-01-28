# API Documentation

## Base URL
```
/api
```

## Authentication

All API endpoints (except login) require authentication using Laravel Sanctum. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### Login
```
POST /api/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": {
            "id": 1,
            "name": "Admin",
            "slug": "admin"
        }
    },
    "token": "1|xxxxxxxxxxxx"
}
```

#### Get Current User
```
GET /api/me
```

**Response:**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": {...},
    "manager": {...}
}
```

#### Logout
```
POST /api/logout
```

---

### Dashboard

#### Get Dashboard Data
```
GET /api/dashboard
```

**Response:**
```json
{
    "stats": {
        "total_leads": 150,
        "new_leads": 25,
        "qualified_leads": 45,
        "closed_won": 30,
        "upcoming_site_visits": 5,
        "pending_followups": 12
    },
    "recent_leads": [...],
    "upcoming_followups": [...],
    "upcoming_site_visits": [...]
}
```

---

### Leads

#### List Leads
```
GET /api/leads
```

**Query Parameters:**
- `status` (optional): Filter by status
- `search` (optional): Search by name, email, or phone
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "status": "new",
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 100
}
```

#### Create Lead
```
POST /api/leads
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "pincode": "10001",
    "source": "website",
    "property_type": "apartment",
    "budget_min": 100000,
    "budget_max": 500000,
    "requirements": "2 BHK apartment",
    "notes": "Interested in downtown area",
    "assigned_to": 5
}
```

#### Get Lead
```
GET /api/leads/{id}
```

#### Update Lead
```
PUT /api/leads/{id}
PATCH /api/leads/{id}
```

**Request Body:** (all fields optional)
```json
{
    "name": "John Doe Updated",
    "status": "qualified",
    "notes": "Updated notes"
}
```

#### Assign Lead
```
POST /api/leads/{id}/assign
```

**Request Body:**
```json
{
    "assigned_to": 5,
    "notes": "Assigning to sales executive"
}
```

---

### Site Visits

#### List Site Visits
```
GET /api/site-visits
```

**Query Parameters:**
- `status` (optional): Filter by status
- `lead_id` (optional): Filter by lead ID
- `per_page` (optional): Items per page

#### Create Site Visit
```
POST /api/site-visits
```

**Request Body:**
```json
{
    "lead_id": 1,
    "assigned_to": 5,
    "property_name": "Sunset Apartments",
    "property_address": "123 Main St",
    "scheduled_at": "2024-01-15 10:00:00",
    "visit_notes": "Show 2BHK unit"
}
```

#### Get Site Visit
```
GET /api/site-visits/{id}
```

#### Update Site Visit
```
PUT /api/site-visits/{id}
PATCH /api/site-visits/{id}
```

**Request Body:**
```json
{
    "status": "completed",
    "completed_at": "2024-01-15 11:30:00",
    "feedback": "Customer liked the property",
    "rating": 5
}
```

---

### Follow-ups

#### List Follow-ups
```
GET /api/follow-ups
```

**Query Parameters:**
- `lead_id` (optional): Filter by lead ID
- `status` (optional): Filter by status
- `per_page` (optional): Items per page

#### Create Follow-up
```
POST /api/follow-ups
```

**Request Body:**
```json
{
    "lead_id": 1,
    "type": "call",
    "notes": "Follow up on property interest",
    "scheduled_at": "2024-01-10 14:00:00"
}
```

#### Update Follow-up
```
PUT /api/follow-ups/{id}
PATCH /api/follow-ups/{id}
```

**Request Body:**
```json
{
    "status": "completed",
    "completed_at": "2024-01-10 14:15:00",
    "outcome": "Customer confirmed interest"
}
```

#### Delete Follow-up
```
DELETE /api/follow-ups/{id}
```

---

### Users (Admin Only)

#### List Users
```
GET /api/users
```

**Query Parameters:**
- `role` (optional): Filter by role slug
- `search` (optional): Search by name or email
- `per_page` (optional): Items per page

#### Create User
```
POST /api/users
```

**Request Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "phone": "+1234567890",
    "role_id": 3,
    "manager_id": 2,
    "is_active": true
}
```

#### Get User
```
GET /api/users/{id}
```

#### Update User
```
PUT /api/users/{id}
PATCH /api/users/{id}
```

#### Delete User
```
DELETE /api/users/{id}
```

---

## Real-Time Events

The system broadcasts real-time events via WebSocket (Pusher). Subscribe to channels to receive updates:

### Channels

- `private-user.{userId}` - User-specific notifications
- `private-leads` - All lead updates (for users with view_all_leads permission)
- `private-site-visits` - Site visit updates

### Events

#### Lead Assigned
```javascript
channel.bind('lead.assigned', function(data) {
    // data.lead - Lead object
    // data.assigned_to - User ID
    // data.assigned_by - User ID
    // data.message - Notification message
});
```

#### Lead Status Updated
```javascript
channel.bind('lead.status.updated', function(data) {
    // data.lead - Lead object
    // data.old_status - Previous status
    // data.new_status - New status
    // data.message - Notification message
});
```

#### Site Visit Created
```javascript
channel.bind('site-visit.created', function(data) {
    // data.site_visit - SiteVisit object
    // data.message - Notification message
});
```

---

## Error Responses

All errors follow this format:

```json
{
    "message": "Error message",
    "errors": {
        "field": ["Error message for field"]
    }
}
```

**Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthenticated
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Role-Based Access

Different roles have different access levels:

- **Admin**: Full access to all endpoints
- **CRM**: Can view all leads, assign leads, manage site visits
- **Sales Manager**: Can view team leads, assign to team members
- **Sales Executive**: Can only view assigned leads
- **Telecaller**: Can only view assigned leads, cannot create site visits

