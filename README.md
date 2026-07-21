# Mandakini Framework

Mandakini Framework is a beginner-friendly PHP web framework designed for fast development of database-driven applications. It offers form helpers, validation, authentication, and automatic database model handling so you can build apps quickly.

## Key features

- Easy PHP MVC-style structure
- Automatic database model support
- Built-in form rendering helpers
- Validation rules for form data
- Login and session management
- Role-based access control support
- Support for MySQL, PostgreSQL, SQL Server, and Oracle
- Simple routing and view handling

## Supported form fields

- Text input
- Password input
- Email input
- Number input
- Hidden input
- Textarea
- Select / dropdown
- Checkbox
- Radio buttons
- File upload
- Date and time fields

## Supported operations

- Automatic CRUD operations using models
- Auto-discovery of database table metadata for supported database engines
- `save()`, `update()`, `delete()`, `findOne()` and `findAll()` on models
- Built-in validation and error reporting
- File upload validation
- User authentication and session handling
- Route-based controller action mapping

## Sample login accounts

- `admin` : `admin123`
- `instructor` : `instructor123`
- `student` : `student123`

## Installation

1. Clone or download the repository.
2. Install Composer dependencies:
   ```bash
   composer install
   ```
3. Configure your database in `public/config.php`.
4. Set your web server document root to the `public/` folder.
5. Open the app in your browser.

## Database support

Mandakini supports multiple database engines through engine-specific model classes:

- `app\core\db\MySqlDBModel`
- `app\core\db\PostgresDBModel`
- `app\core\db\MSSQLServerDBModel`
- `app\core\db\OracleDBModel`

These subclasses automatically detect table columns and primary keys when possible.

## Project structure

- `public/` – application entry point, routes, and configuration
- `core/` – framework internals
- `controllers/` – request handlers and page logic
- `models/` – data models and validation
- `views/` – HTML templates and layout files
- `migrations/` – database migration scripts
- `vendor/` – Composer dependencies

## User manual

A full user manual is available in `USERMANUAL.md` with setup instructions, examples, and detailed guidance for beginners.

## Change log

- 17/07/2026
  - Added PostgreSQL support
  - Added SQL Server support
  - Added Oracle support
- 21/07/2026
  - Moved routes out of `index.php` into `public/routes.php` for cleaner structure
  - usermanual is written it will be available soon
