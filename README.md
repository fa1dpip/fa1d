# User Management System

This project implements the assignment from `TASK user connection.pdf` as a PHP web application with:

- namespaces, an abstract class, an interface, and a trait
- session-based login and logout
- SQLite database storage for users and activity logs
- separate Admin and Regular User roles with different permissions
- password hashing with `password_hash()` and validation with `password_verify()`

## Structure

- `app/Core` contains the abstract class, interface, trait, database, and session manager
- `app/Models` contains `Admin` and `RegularUser`
- `app/Repositories` contains the SQLite persistence layer
- `app/Services` contains authentication, registration, and model hydration
- `public` contains the web interface
- `tests/integration.php` contains a CLI verification script

## Default Admin

- Email: `alice@example.com`
- Password: `admin123`

The admin account is seeded automatically the first time the application starts.

## Run Locally

1. Start the PHP development server:

   ```powershell
   C:\php\php.exe -c php.ini -S 127.0.0.1:8000 -t public
   ```

2. Open `http://127.0.0.1:8000`.

3. Use the admin credentials above or register a regular user.

## Run Tests

```powershell
C:\php\php.exe -c php.ini tests\integration.php
```
