# User Management System

The Portfolio CMS now includes a complete user management system with role-based access control.

## User Roles

### Admin
- Full access to all features
- Can manage projects, users, settings
- Can assign project access to clients
- Access admin panel at `/admin/`

### Client
- Limited access to assigned projects only
- Can view projects they have access to
- Cannot edit or delete anything
- Access client portal at `/client/`

### Family
- **Schedule access only** - can view work schedule
- No project access (use public portfolio instead)
- Cannot edit or delete anything
- Access family portal at `/family/`
- Ideal for: Family members who need to coordinate schedules and see availability

## Features

### For Administrators

**User Management** (`/admin/users.php`):
- Create new users (admin or client)
- Edit user information
- Activate/deactivate accounts
- Delete client accounts
- View user statistics

**Project Access Control** (in project edit page):
- Assign projects to specific clients
- Grant view permissions
- Grant download permissions
- Remove access when needed

### For Clients

**Client Portal** (`/client/dashboard.php`):
- View assigned projects only
- See project details, images, and information
- Download files (if permission granted)
- Update profile information

## Creating Client Accounts

### Method 1: Admin Creates Account
1. Login as admin
2. Go to **Users** → **Add New User**
3. Fill in client information:
   - Username (required)
   - Email (required)
   - Password (required, min 6 characters)
   - First Name, Last Name
   - Company name
   - Phone number
   - Internal notes
4. Set Role to **Client** (for business clients) or **Family** (for family members who need schedule access)
5. Set Status to **Active**
6. Click **Create User**
7. Share credentials with the client/family member

**Note**: 
- Family members can view the work schedule but not projects
- Family members should use the public portfolio to view work
- Clients can view only projects assigned to them

### Method 2: Self-Registration (Future Feature)
Can be implemented to allow clients to register themselves with admin approval.

## Assigning Projects to Clients

**Note**: 
- Family members don't have access to the project system - they only see the work schedule
- Family members can view the public portfolio like any website visitor
- Only Client users need project assignments

For **Client** users only:

1. Login as admin
2. Go to **Projects** and edit a project
3. Scroll down to **Client Access** section
4. Check **Can View** for clients who should see this project
5. Check **Can Download** for clients who can download files
6. Click **Update Access**

## Client Login Process

1. Client visits `/admin/login.php`
2. Enters username and password
3. Automatically redirected to `/client/dashboard.php`
4. Views only projects they have access to

## User Statuses

- **Active**: User can login and access the system
- **Inactive**: User cannot login (account suspended)
- **Pending**: User account awaiting approval (future feature)

## Security Features

- Password hashing with bcrypt
- Role-based access control
- Session management
- SQL injection prevention
- XSS protection
- Access control on all pages

## Database Schema

### Users Table
```sql
- id (primary key)
- username (unique)
- email (unique)
- password (hashed)
- first_name
- last_name
- role (admin/client)
- status (active/inactive/pending)
- company
- phone
- notes
- last_login
- created_at
- updated_at
```

### Project Access Table
```sql
- id (primary key)
- project_id (foreign key)
- user_id (foreign key)
- can_view (boolean)
- can_download (boolean)
- created_at
```

## Common Tasks

### Reset a Client's Password
1. Go to **Users**
2. Find the client and click **Edit**
3. Enter new password
4. Confirm password
5. Save changes

### Remove Client Access to Project
1. Edit the project
2. Uncheck **Can View** for that client
3. Click **Update Access**

### Deactivate a Client Account
1. Go to **Users**
2. Find the client
3. Click **Deactivate**
4. They will no longer be able to login

### View Which Clients Have Access to a Project
1. Edit the project
2. Scroll to **Client Access** section
3. See checkmarks for clients with access

## Best Practices

1. **Use Strong Passwords**: Require at least 8 characters with mixed case and numbers
2. **Regular Audits**: Periodically review user access and remove unused accounts
3. **Limit Admin Accounts**: Only create admin accounts for trusted staff
4. **Document Access**: Use the notes field to document why clients have access
5. **Deactivate vs Delete**: Deactivate accounts instead of deleting for record keeping

## Customization

### Add Custom Fields
Edit `schema.sql` and add columns to the users table:
```sql
ALTER TABLE users ADD COLUMN your_field VARCHAR(255);
```

Update `user-add.php` and `user-edit.php` to include the new field.

### Change Password Requirements
Edit `user-add.php` and modify the validation:
```php
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
```

### Custom Client Dashboard
Edit `/client/dashboard.php` to add:
- Custom reports
- File downloads
- Communication features
- Project feedback forms

## Troubleshooting

### Client Can't See Any Projects
- Check if projects are assigned in **Project Access**
- Verify client status is **Active**
- Confirm client is logging in (not admin account)

### Client Sees Admin Panel
- Check user role is set to **Client** (not Admin)
- Have client logout and login again

### Can't Delete a User
- Only client accounts can be deleted
- Admin accounts can only be deactivated
- Ensure you're not trying to delete your own account

## Future Enhancements

Potential features to add:
- Self-registration with approval workflow
- Email notifications
- Project comments/feedback
- File upload by clients
- Activity logs
- Two-factor authentication
- Password reset via email

## Support

For issues or questions about user management, check the main README.md or LOGIN-HELP.md files.
