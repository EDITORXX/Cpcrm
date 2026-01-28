# System Architecture

## Overview

The Real Estate CRM is built using Laravel 10 with a clean MVC architecture, event-driven design, and real-time capabilities.

## Technology Stack

### Backend
- **Framework**: Laravel 10.x
- **PHP**: 8.1+
- **Database**: MySQL 5.7+
- **Cache/Queue**: Redis
- **Authentication**: Laravel Sanctum
- **Real-Time**: Pusher WebSockets

### Frontend
- **Templating**: Laravel Blade
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS + Axios
- **Real-Time Client**: Pusher JS

## Architecture Patterns

### 1. MVC (Model-View-Controller)

**Models** (`app/Models/`)
- Handle database interactions
- Define relationships
- Contain business logic methods
- Use Eloquent ORM

**Views** (`resources/views/`)
- Blade templates
- Separate presentation logic
- Reusable components

**Controllers** (`app/Http/Controllers/`)
- Handle HTTP requests
- Validate input
- Call models/services
- Return responses

### 2. Event-Driven Architecture

**Events** (`app/Events/`)
- Define system events
- Implement `ShouldBroadcast` for real-time
- Carry event data

**Listeners** (`app/Listeners/`)
- React to events
- Perform side effects
- Send notifications
- Queue heavy operations

**Benefits**:
- Decoupled components
- Easy to extend
- Scalable
- Testable

### 3. Middleware Pattern

**Role Middleware** (`CheckRole`)
- Verify user roles
- Protect routes
- Return 403 if unauthorized

**Permission Middleware** (`CheckPermission`)
- Check specific permissions
- Role-based logic
- Flexible authorization

**Activity Logging** (`LogActivity`)
- Audit trail
- Track user actions
- Security monitoring

### 4. Repository Pattern (Recommended for Future)

For complex queries, consider implementing repositories:

```php
interface LeadRepositoryInterface {
    public function getLeadsForUser(User $user);
    public function searchLeads(string $query);
}

class LeadRepository implements LeadRepositoryInterface {
    // Complex query logic
}
```

## Data Flow

### API Request Flow

1. **Request** → Route (`routes/api.php`)
2. **Middleware** → Authentication & Authorization
3. **Controller** → Handle request
4. **Model** → Database operations
5. **Event** → Trigger events (if needed)
6. **Response** → JSON response

### Real-Time Flow

1. **Event Fired** → `event(new LeadAssigned(...))`
2. **Broadcast** → Pusher WebSocket
3. **Client Receives** → JavaScript handler
4. **UI Updates** → DOM manipulation

## Security Architecture

### Authentication Layer
- Laravel Sanctum tokens
- Token expiration
- Secure token storage

### Authorization Layer
- Role-based checks
- Permission-based checks
- Route protection
- Resource-level authorization

### Data Protection
- Input validation
- SQL injection prevention (Eloquent)
- XSS protection (Blade)
- CSRF tokens
- Rate limiting

## Database Design

### Normalization
- 3NF normalized
- Proper foreign keys
- Indexed columns
- Soft deletes for audit

### Relationships
- One-to-Many: User → Leads
- Many-to-Many: Leads ↔ Users (via assignments)
- Polymorphic: ActivityLogs

### Indexing Strategy
- Foreign keys indexed
- Frequently queried columns
- Composite indexes for common queries

## Scalability Considerations

### Horizontal Scaling
- Stateless API design
- Redis for shared state
- Database read replicas
- Load balancer ready

### Vertical Scaling
- Query optimization
- Eager loading
- Caching strategy
- Queue processing

### Caching Strategy
- User permissions cached
- Dashboard stats cached
- Frequently accessed data
- Cache invalidation

### Queue Strategy
- Heavy operations queued
- Notification sending
- Report generation
- Background processing

## API Design

### RESTful Principles
- Resource-based URLs
- HTTP methods (GET, POST, PUT, DELETE)
- Status codes
- Consistent responses

### Response Format
```json
{
    "data": {...},
    "message": "...",
    "errors": {...}
}
```

### Error Handling
- Consistent error format
- Proper HTTP status codes
- Validation errors
- Exception handling

## Real-Time Architecture

### Broadcasting
- Private channels
- Presence channels (future)
- Event broadcasting
- Notification broadcasting

### Channels
- User-specific: `private-user.{id}`
- Resource-specific: `private-leads`
- Team-specific: `private-team.{id}` (future)

### Client Integration
- Pusher JS library
- Channel subscription
- Event binding
- Reconnection handling

## Testing Strategy

### Unit Tests
- Model methods
- Helper functions
- Business logic

### Feature Tests
- API endpoints
- Authentication
- Authorization
- Integration flows

### Test Coverage
- Critical paths
- Security features
- Business logic
- Edge cases

## Deployment Architecture

### Development
- Local server
- Local database
- Local Redis
- Pusher sandbox

### Production
- Multiple app servers
- Database master-slave
- Redis cluster
- CDN for assets
- Queue workers
- WebSocket server

## Monitoring & Logging

### Application Logs
- Laravel logs
- Error tracking
- Performance monitoring

### Activity Logs
- User actions
- System events
- Audit trail

### Metrics
- Response times
- Error rates
- Queue length
- Database performance

## Future Architecture Enhancements

### Microservices (Optional)
- Separate services for:
  - User management
  - Lead management
  - Notifications
  - Reporting

### API Gateway
- Single entry point
- Rate limiting
- Authentication
- Request routing

### Message Queue
- RabbitMQ or AWS SQS
- Event sourcing
- CQRS pattern

### Caching Layer
- Redis cluster
- CDN caching
- Application cache
- Database query cache

