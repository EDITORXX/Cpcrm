# User Credentials

## Created Users

### 1. CRM User
- **Name:** CRM Manager
- **Email:** crm@realtorcrm.com
- **Password:** crm123
- **Role:** CRM
- **Access:** Can import leads, create leads, assign leads, manage assignment rules

### 2. Sales Head
- **Name:** Sales Head
- **Email:** saleshead@realtorcrm.com
- **Password:** saleshead123
- **Role:** Sales Manager
- **Access:** Can view all team leads, assign leads to team members

### 3. Sales Managers (2 users)
- **Name:** Sales Manager 1
- **Email:** salesmanager1@realtorcrm.com
- **Password:** sm123
- **Role:** Sales Manager
- **Manager:** Sales Head
- **Team:** Telecaller 1, Telecaller 2

---

- **Name:** Sales Manager 2
- **Email:** salesmanager2@realtorcrm.com
- **Password:** sm123
- **Role:** Sales Manager
- **Manager:** Sales Head
- **Team:** Telecaller 3, Telecaller 4

### 4. Telecallers (4 users)
- **Name:** Telecaller 1
- **Email:** telecaller1@realtorcrm.com
- **Password:** tc123
- **Role:** Telecaller
- **Manager:** Sales Manager 1

---

- **Name:** Telecaller 2
- **Email:** telecaller2@realtorcrm.com
- **Password:** tc123
- **Role:** Telecaller
- **Manager:** Sales Manager 1

---

- **Name:** Telecaller 3
- **Email:** telecaller3@realtorcrm.com
- **Password:** tc123
- **Role:** Telecaller
- **Manager:** Sales Manager 2

---

- **Name:** Telecaller 4
- **Email:** telecaller4@realtorcrm.com
- **Password:** tc123
- **Role:** Telecaller
- **Manager:** Sales Manager 2

## Organizational Structure

```
Sales Head
├── Sales Manager 1
│   ├── Telecaller 1
│   └── Telecaller 2
└── Sales Manager 2
    ├── Telecaller 3
    └── Telecaller 4

CRM Manager (Independent)
```

## Login URLs

- **Web Login:** http://localhost:8007/login
- **API Login:** http://localhost:8007/api/login

## Quick Access

### CRM Features
- Automation Dashboard: http://localhost:8007/crm/automation
- Create Lead: http://localhost:8007/crm/automation/leads/create
- Import Leads: http://localhost:8007/crm/automation/import
- Manage Rules: http://localhost:8007/crm/automation/rules

## Security Note

**Please change all passwords after first login!**

All users are set to active status and ready to use.

