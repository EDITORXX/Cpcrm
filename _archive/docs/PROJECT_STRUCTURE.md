# Project Structure

## Directory Structure

```
real-estate-crm/
├── app/
│   ├── Events/
│   │   ├── LeadAssigned.php
│   │   ├── LeadStatusUpdated.php
│   │   └── SiteVisitCreated.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── FollowUpController.php
│   │   │   │   ├── LeadController.php
│   │   │   │   ├── SiteVisitController.php
│   │   │   │   └── UserController.php
│   │   │   └── Controller.php
│   │   └── Middleware/
│   │       ├── CheckPermission.php
│   │       ├── CheckRole.php
│   │       └── LogActivity.php
│   ├── Listeners/
│   │   ├── SendLeadAssignedNotification.php
│   │   └── SendSiteVisitCreatedNotification.php
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── FollowUp.php
│   │   ├── Lead.php
│   │   ├── LeadAssignment.php
│   │   ├── Role.php
│   │   ├── SiteVisit.php
│   │   └── User.php
│   ├── Notifications/
│   │   ├── LeadAssignedNotification.php
│   │   └── SiteVisitCreatedNotification.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── BroadcastServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RouteServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── broadcasting.php
│   ├── database.php
│   └── sanctum.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_users_table.php
│   │   ├── 2024_01_01_000003_create_leads_table.php
│   │   ├── 2024_01_01_000004_create_lead_assignments_table.php
│   │   ├── 2024_01_01_000005_create_site_visits_table.php
│   │   ├── 2024_01_01_000006_create_follow_ups_table.php
│   │   ├── 2024_01_01_000007_create_notifications_table.php
│   │   ├── 2024_01_01_000008_create_activity_logs_table.php
│   │   └── 2024_01_01_000009_create_personal_access_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RoleSeeder.php
├── routes/
│   ├── api.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── resources/
│   └── views/
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── .env.example
├── .gitignore
├── composer.json
├── phpunit.xml
├── README.md
├── API_DOCUMENTATION.md
├── INSTALLATION.md
├── PROJECT_STRUCTURE.md
└── SCALING.md
```

## Architecture Overview

### MVC Pattern

- **Models**: Located in `app/Models/` - Handle database interactions and business logic
- **Views**: Located in `resources/views/` - Blade templates for web interface
- **Controllers**: Located in `app/Http/Controllers/` - Handle HTTP requests and responses

### Event-Driven Architecture

- **Events**: Located in `app/Events/` - Define system events
- **Listeners**: Located in `app/Listeners/` - Handle event responses
- **Notifications**: Located in `app/Notifications/` - Send notifications to users

### API Structure

All API controllers are in `app/Http/Controllers/Api/`:
- RESTful resource controllers
- Consistent response format
- Role-based access control

### Middleware

- **CheckRole**: Verify user has specific role
- **CheckPermission**: Verify user has specific permission
- **LogActivity**: Log user actions for audit trail

## Database Schema

### Core Tables

1. **roles** - User roles (Admin, CRM, Sales Manager, etc.)
2. **users** - System users with role assignments
3. **leads** - Customer leads
4. **lead_assignments** - Lead assignment history
5. **site_visits** - Property site visits
6. **follow_ups** - Follow-up tasks
7. **notifications** - User notifications
8. **activity_logs** - System activity audit log

### Relationships

- Users belong to Roles
- Users can have Managers (self-referential)
- Leads belong to Users (creator)
- Leads have many LeadAssignments
- Leads have many SiteVisits
- Leads have many FollowUps
- SiteVisits belong to Leads and Users
- FollowUps belong to Leads and Users

## Security

### Authentication
- Laravel Sanctum for API authentication
- Token-based authentication
- Secure password hashing

### Authorization
- Role-based access control (RBAC)
- Permission-based middleware
- Route protection

### Data Protection
- Input validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- CSRF protection

## Real-Time Features

### Broadcasting
- Pusher integration for WebSocket
- Private channels for user-specific updates
- Event broadcasting for system-wide updates

### Channels
- `private-user.{userId}` - User notifications
- `private-leads` - Lead updates
- `private-site-visits` - Site visit updates

## Best Practices

1. **Service Layer**: Consider adding service classes for complex business logic
2. **Repository Pattern**: Use repositories for complex queries
3. **Form Requests**: Create form request classes for validation
4. **API Resources**: Use API resources for consistent response formatting
5. **Caching**: Implement caching for frequently accessed data
6. **Queue Jobs**: Use queues for heavy operations
7. **Testing**: Write unit and feature tests
8. **Documentation**: Keep API documentation updated

## Future Enhancements

1. **Mobile App Integration**: APIs are ready for Flutter/mobile apps
2. **AI Features**: Structure supports AI integration
3. **Reporting**: Add advanced reporting module
4. **Analytics**: Integrate analytics dashboard
5. **Multi-tenancy**: Support for multiple companies
6. **Document Management**: File upload and management
7. **Email Integration**: Email tracking and templates
8. **Calendar Integration**: Sync with Google Calendar

