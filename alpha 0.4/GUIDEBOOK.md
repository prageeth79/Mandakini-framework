# Mandakini Framework Guide Book

This guide book is designed to train new developers, help teams understand the framework quickly, and serve as a practical handbook for building applications with Mandakini.

It explains the framework architecture, feature set, coding patterns, and best practices used in this project.

## Table of Contents

### Getting Started
- [1. Purpose of the Framework](#1-purpose-of-the-framework)
- [2. What the Framework Gives You](#2-what-the-framework-gives-you)
- [3. Framework Philosophy](#3-framework-philosophy)
- [4. Project Structure](#4-project-structure)
- [5. Core Concepts](#5-core-concepts)

### Core Concepts & Components
- [6. Controllers](#6-controllers)
- [7. Models and Validation](#7-models-and-validation)
- [8. Database Models](#8-database-models)
- [9. Views and Layouts](#9-views-and-layouts)
- [10. Form Builder](#10-form-builder)

### Application Features
- [11. Authentication and Session Management](#11-authentication-and-session-management)
- [12. Middleware](#12-middleware)
- [13. Request and Response Lifecycle](#13-request-and-response-lifecycle)
- [14. Configuration](#14-configuration)
- [15. Routing Deep Dive](#15-routing-deep-dive)
- [16. Database Migrations](#16-database-migrations)

### Security & Best Practices
- [17. Security Considerations](#17-security-considerations)
- [18. Best Practices for Training and Team Use](#18-best-practices-for-training-and-team-use)

### Learning & Training
- [19. Typical Feature Development Flow](#19-typical-feature-development-flow)
- [20. Suggested Training Path for New Developers](#20-suggested-training-path-for-new-developers)
- [21. Labs and Exercises](#21-labs-and-exercises)
- [22. Common Problems and Solutions](#22-common-problems-and-solutions)

### Reference & Quick Start
- [23. Quick Reference - Essential Commands](#23-quick-reference---essential-commands)
- [24. Recommended Training Message to New Developers](#24-recommended-training-message-to-new-developers)
- [25. Next Steps for Learning](#25-next-steps-for-learning)
- [26. Key Resources](#26-key-resources)

---

## 1. Purpose of the Framework

Mandakini is a lightweight PHP MVC-style web framework created for:

- fast application development
- simple route-based architecture
- beginner-friendly coding conventions
- model-driven validation
- database access with minimal boilerplate
- authentication and role-based restrictions

It is intended for projects where the goal is to build web applications quickly without the complexity of larger frameworks.

## 2. What the Framework Gives You

Mandakini includes the following core capabilities:

- HTTP routing
- controller-based request handling
- model validation
- database CRUD helpers
- form generation helpers
- templated views and layouts
- session handling
- login/authentication flow
- middleware-based access control
- database migration support
- multi-database compatibility

This is enough to build simple business applications, learning platforms, admin panels, portals, and internal management systems.

## 3. Framework Philosophy

Mandakini follows a simple rule:

- route receives the request
- controller handles the business logic
- model validates and stores data
- view renders the output

The goal is not to hide logic, but to keep code organized and easy to understand.

In practice, the project encourages straightforward development patterns:

- keep URL logic in `public/routes.php`
- keep request logic in controller actions
- keep validation rules in model classes
- keep HTML in `views/`
- keep reusable page structure in layouts

## 4. Project Structure

```text
/
├── public/
│   ├── config.php
│   ├── index.php
│   └── routes.php
├── core/
│   ├── Application.php
│   ├── Controller.php
│   ├── Model.php
│   ├── Router.php
│   ├── Request.php
│   ├── Response.php
│   ├── Session.php
│   ├── View.php
│   ├── UserModel.php
│   ├── db/
│   └── form/
├── controllers/
├── models/
├── views/
├── migrations/
├── vendor/
├── composer.json
├── README.md
├── HELP.md
├── USERMANUAL.md
├── GUIDEBOOK.md
```

## 5. Core Concepts

### 5.1 Application entry point

The app begins in `public/index.php`.

This file:

1. loads Composer autoloading
2. starts a session
3. creates the `Application` instance
4. loads route definitions
5. runs the app

Example:

```php
$app = new Application(dirname(__DIR__), $config);
Application::$app = $app;

include_once __DIR__ . '/routes.php';

echo $app->run();
```

This is the main bootstrapping point for the application.

### 5.2 Application object

`core/Application.php` creates the central application instance. It stores:

- the router
- the request
- the response
- the session
- current controller
- current logged in user
- app configuration

It is the hub of the framework.

### 5.3 Router

The router matches a URL to a controller action.

It supports:

- static routes
- GET and POST routes
- parameterized routes like `/course/{id}`

Example:

```php
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->post('/login', [AuthController::class, 'login']);
$app->router->get('/course/{id}', [SiteController::class, 'courses']);
```

When a route includes a placeholder like `{id}`, the router extracts the value and stores it in `$_GET` and `$_REQUEST`.

### 5.4 Request object

`Request` is responsible for reading the current request details.

Available methods include:

```php
$request->getPath();
$request->method();
$request->isGet();
$request->isPost();
$request->getBody();
```

This is used heavily in controllers to process user input.

### 5.5 Response object

The response layer handles application responses such as redirects and status codes.

Example:

```php
Application::$app->response->redirect('/');
```

### 5.6 Session object

`Session` stores values between requests. This is commonly used for:

- login state
- flash messages
- current user session values

Example:

```php
Application::$app->session->setFlash('success', 'Thanks for registering');
```

## 6. Controllers

Controllers are placed inside `controllers/` and must extend `app\core\Controller`.

A controller is responsible for handling a request and choosing a response.

### 6.1 Example controller

```php
<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Request;

class SiteController extends Controller {
    public function homeAction() {
        return $this->render('home', [
            'title' => 'Welcome',
        ]);
    }
}
```

### 6.2 Controller conventions

- class name ends with `Controller`
- method name ends with `Action`
- route points to class and method without `Action`
- logic is placed in controller actions
- views are rendered using `render()`

### 6.3 Real-world controller example

This example shows a complete controller handling registration, login, and profile pages:

```php
<?php
namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\User;
use app\models\LoginForm;
use app\models\RegisterForm;

class AuthController extends Controller {
    
    // Show the registration form
    public function registerAction() {
        $model = new RegisterForm();
        
        // If form was submitted via POST
        if (Application::$app->request->isPost()) {
            // Load form data from the request
            $model->loadData(Application::$app->request->getBody());
            
            // Validate the data
            if ($model->validate()) {
                // Create a new user in the database
                $user = new User();
                $user->firstName = $model->firstName;
                $user->loging_id = $model->loging_id;
                $user->email = $model->email;
                $user->password = password_hash($model->password, PASSWORD_DEFAULT);
                
                if ($user->save()) {
                    // Login the user automatically
                    Application::$app->login($user);
                    
                    // Redirect to profile
                    Application::$app->response->redirect('/profile');
                    return '';
                }
            }
        }
        
        // Show the registration view with form
        return $this->render('register', ['model' => $model]);
    }
    
    // Show and handle the login form
    public function loginAction() {
        $model = new LoginForm();
        
        if (Application::$app->request->isPost()) {
            $model->loadData(Application::$app->request->getBody());
            
            if ($model->validate()) {
                // Find user by login ID
                $user = User::findOne(['loging_id' => $model->loging_id]);
                
                if ($user && password_verify($model->password, $user->password)) {
                    // Login successful
                    Application::$app->login($user);
                    Application::$app->session->setFlash('success', 'Welcome back!');
                    Application::$app->response->redirect('/profile');
                    return '';
                } else {
                    // Login failed
                    $model->addError('loging_id', 'Invalid credentials');
                }
            }
        }
        
        return $this->render('login', ['model' => $model]);
    }
    
    // Show the logged-in user's profile
    public function profileAction() {
        // This action is protected by middleware (see 6.5)
        $user = Application::$app->user;
        
        return $this->render('profile', [
            'user' => $user,
            'title' => $user->firstName . ' Profile',
        ]);
    }
    
    // Logout the current user
    public function logoutAction() {
        Application::$app->logout();
        Application::$app->session->setFlash('success', 'You have been logged out');
        Application::$app->response->redirect('/');
        return '';
    }
}
```

This example shows the complete lifecycle of a controller action.

### 6.4 Controller layout support

Controllers can call `setLayout()` to switch layouts for a specific action:

```php
public function loginAction() {
    // Use the 'auth' layout instead of the default 'main' layout
    $this->setLayout('auth');
    
    $model = new LoginForm();
    return $this->render('login', ['model' => $model]);
}

public function homeAction() {
    // This will use the default 'main' layout
    return $this->render('home', ['title' => 'Welcome']);
}
```

### 6.5 Middleware support - Protecting pages

A controller can register middleware to protect specific actions from unauthorized access:

```php
// Protect the profile action - only logged-in users can access it
public function profileAction() {
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware([]));
    
    $user = Application::$app->user;
    return $this->render('profile', ['user' => $user]);
}

// Protection with role restrictions - only admins can access
public function adminAction() {
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['admin']));
    
    // Get all users
    $users = User::findAll();
    return $this->render('admin', ['users' => $users]);
}

// Allow multiple roles
public function instructorPanelAction() {
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['instructor', 'admin']));
    
    $courses = Course::where('instructor_id', '=', Application::$app->user->id);
    return $this->render('instructor_panel', ['courses' => $courses]);
}
```

Middleware runs before the action is executed. If the user is not authorized, it blocks access and redirects to login.

## 7. Models and Validation

Models are stored in `models/` and extend `app\core\Model`.

A model usually represents:

- a form
- a data object
- a database-backed entity

### 7.1 Basic model example - LoginForm

This model handles login form validation:

```php
<?php
namespace app\models;

use app\core\Model;

class LoginForm extends Model {
    public string $loging_id = '';
    public string $password = '';

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
        ];
    }
    
    public function labels(): array {
        return [
            'loging_id' => 'Login ID or Email',
            'password' => 'Password',
        ];
    }
}
```

### 7.2 Advanced model example - RegisterForm

This example shows more validation rules:

```php
<?php
namespace app\models;

use app\core\Model;

class RegisterForm extends Model {
    public string $firstName = '';
    public string $lastName = '';
    public string $loging_id = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';

    public function rules(): array {
        return [
            'firstName' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2]],
            'lastName' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2]],
            'loging_id' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 20],
                [self::RULE_UNIQUE, 'class' => User::class]
            ],
            'email' => [
                self::RULE_REQUIRED,
                self::RULE_EMAIL,
                [self::RULE_UNIQUE, 'class' => User::class]
            ],
            'password' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 8],
                [self::RULE_MAX, 'max' => 128],
            ],
            'confirmPassword' => [
                self::RULE_REQUIRED,
                [self::RULE_MATCH, 'match' => 'password']
            ],
        ];
    }
    
    public function labels(): array {
        return [
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'loging_id' => 'Username',
            'email' => 'Email Address',
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }
}
```

### 7.3 How to use models in controllers

```php
public function registerAction() {
    $model = new RegisterForm();
    
    // Load data from the form submission
    $model->loadData(Application::$app->request->getBody());
    
    // Validate the data
    if ($model->validate()) {
        // All validation passed - save to database
        // Process the data here
    } else {
        // Validation failed - show form with errors
        echo $model->getFirstError('email');  // "Email must be valid"
    }
}
```

### 7.4 `loadData()` - Loading form input

This method loads POST data into model properties:

```php
// If form was submitted with POST method containing:
// _POST = ['email' => 'user@example.com', 'password' => '12345678']

$model->loadData(Application::$app->request->getBody());

// Now the model has:
// $model->email = 'user@example.com'
// $model->password = '12345678'
```

### 7.5 `validate()` - Checking data

The framework validates each field based on the rules array:

```php
if ($model->validate()) {
    // Data is valid - all rules passed
    echo "Form is valid!";
} else {
    // Show errors
    foreach ($model->errors as $field => $messages) {
        echo "$field: " . implode(', ', $messages);
    }
}
```

### 7.6 Available validation rules - Complete reference

The framework supports these built-in rules:

| Rule | Usage | Example |
|------|-------|---------|
| `RULE_REQUIRED` | Field cannot be empty | `[self::RULE_REQUIRED]` |
| `RULE_EMAIL` | Must be valid email | `[self::RULE_EMAIL]` |
| `RULE_MIN` | Minimum length/value | `[self::RULE_MIN, 'min' => 8]` |
| `RULE_MAX` | Maximum length/value | `[self::RULE_MAX, 'max' => 100]` |
| `RULE_MATCH` | Must match another field | `[self::RULE_MATCH, 'match' => 'password']` |
| `RULE_UNIQUE` | Value must be unique in database | `[self::RULE_UNIQUE, 'class' => User::class]` |
| `RULE_NUMERIC` | Must be a number | `[self::RULE_NUMERIC]` |
| `RULE_INT` | Must be an integer | `[self::RULE_INT]` |
| `RULE_FLOAT` | Must be a decimal number | `[self::RULE_FLOAT]` |
| `RULE_DATE` | Must be valid date | `[self::RULE_DATE]` |
| `RULE_INLIST` | Must be in given list | `[self::RULE_INLIST, 'values' => ['admin', 'user']]` |
| `RULE_REGEX` | Must match regex pattern | `[self::RULE_REGEX, 'pattern' => '/^[A-Z]+$/']` |
| `RULE_ALPHA` | Only letters | `[self::RULE_ALPHA]` |
| `RULE_ALPHA_PLUS_SPACE` | Letters and spaces | `[self::RULE_ALPHA_PLUS_SPACE]` |
| `RULE_ALPHA_PLUS_SPACE_PLUS_DOT` | Letters, spaces, dots | `[self::RULE_ALPHA_PLUS_SPACE_PLUS_DOT]` |
| `RULE_ALPHANUMARIC` | Letters and numbers | `[self::RULE_ALPHANUMARIC]` |
| `RULE_ALPHANUMARIC_PLUS_SPACE` | Letters, numbers, spaces | `[self::RULE_ALPHANUMARIC_PLUS_SPACE]` |

### 7.7 Error handling in models - Getting and displaying errors

The framework stores validation messages in the `errors` property:

```php
// Check if a specific field has errors
if ($model->hasError('email')) {
    echo "Email field has an error";
}

// Get the first error for a field
$firstError = $model->getFirstError('email');
echo $firstError;  // "Email must be valid"

// Get all errors for a field
$allErrors = $model->getErrors('email');  // Array of error messages

// Display in a form
<div class="form-group">
    <label>Email</label>
    <input type="email" name="email" value="<?php echo $model->email; ?>">
    <?php if ($model->hasError('email')): ?>
        <div class="error"><?php echo $model->getFirstError('email'); ?></div>
    <?php endif; ?>
</div>
```

### 7.8 Labels and user-friendly names - Better form output

`labels()` lets you set readable field labels for display in forms and error messages:

```php
public function labels(): array {
    return [
        'loging_id' => 'Login ID',
        'firstName' => 'First Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
    ];
}
```

This improves form output and validation messages. Instead of showing "loging_id", it shows "Login ID".

## 8. Database Models

Database models are located in `models/` and extend `DBModel`.

This is the database-aware layer that gives the app persistence and query features.

### 8.1 Database model contract - Required methods

A database model must define these methods:

- `tableName()` - Returns the database table name
- `attributes()` - Returns an array of column names
- `primaryKey()` - Returns the primary key column name
- `rules()` - Returns validation rules

Example:

```php
<?php
namespace app\models;

use app\core\db\DBModel;

class User extends DBModel {
    public string $loging_id = '';
    public string $email = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $password = '';
    public string $category = 'student';
    public string $created_at = '';
    public string $updated_at = '';

    public static function tableName(): string {
        return 'users';
    }

    public function attributes(): array {
        return ['loging_id', 'email', 'firstName', 'lastName', 'password', 'category', 'created_at', 'updated_at'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 3]],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'firstName' => [self::RULE_REQUIRED],
            'lastName' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
        ];
    }
}
```

### 8.2 Complete user model example

```php
<?php
namespace app\models;

use app\core\db\DBModel;

class User extends DBModel {
    public string $loging_id = '';
    public string $email = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $password = '';
    public string $category = 'student';

    public static function tableName(): string {
        return 'users';  // Database table name
    }

    public function attributes(): array {
        // These match the database columns
        return ['loging_id', 'email', 'firstName', 'lastName', 'password', 'category'];
    }

    public static function primaryKey(): string {
        return 'loging_id';  // Primary key column
    }

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'firstName' => [self::RULE_REQUIRED],
            'lastName' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED],
        ];
    }
    
    public function labels(): array {
        return [
            'loging_id' => 'Login ID',
            'email' => 'Email Address',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'password' => 'Password',
            'category' => 'User Role',
        ];
    }
    
    public function getDisplayName() {
        return $this->firstName . ' ' . $this->lastName;
    }
}
```

### 8.3 Save, update, and delete operations

```php
// CREATE - Save a new record
$user = new User();
$user->loging_id = 'john_doe';
$user->email = 'john@example.com';
$user->firstName = 'John';
$user->lastName = 'Doe';
$user->password = password_hash('securepass', PASSWORD_DEFAULT);
$user->category = 'student';

if ($user->save()) {
    echo "User saved successfully";
} else {
    echo "Error: " . implode(', ', $user->getErrors());
}

// READ - Fetch a user
$user = User::findOne(['loging_id' => 'john_doe']);

// UPDATE - Modify and save
$user->email = 'john.doe@example.com';
$user->category = 'instructor';
$user->update(['loging_id' => 'john_doe']);

// DELETE - Remove a record
$user->delete();
```

### 8.4 Query helper methods - Finding records

The `DBModel` includes powerful static query methods:

```php
// Find a single record by primary key
$user = User::findOne(['loging_id' => 'admin']);

// Example result: User object with all properties populated
echo $user->email;      // admin@example.com
echo $user->firstName;  // Admin

// Find all records
$allUsers = User::findAll();

// Loop through results
foreach ($allUsers as $user) {
    echo $user->getDisplayName() . "\n";
}

// Find with WHERE condition
$students = User::where('category', '=', 'student');

// Multiple conditions
$instructors = User::where('category', '=', 'instructor')
                    ->where('firstName', '=', 'John');

// Using comparison operators
$newUsers = User::where('created_at', '>=', '2024-01-01');

// Find and count
if (User::findOne(['email' => $email])) {
    echo "Email already exists";
}
```

### 8.5 Transaction support - Multiple operations

Transactions ensure that multiple database changes either all succeed together or all fail together:

```php
// Example: Transfer credits between users
User::transaction(function () {
    // Deduct credits from one user
    $sender = User::findOne(['loging_id' => 'user1']);
    $sender->credits = $sender->credits - 100;
    $sender->update(['loging_id' => 'user1']);
    
    // Add credits to another user
    $receiver = User::findOne(['loging_id' => 'user2']);
    $receiver->credits = $receiver->credits + 100;
    $receiver->update(['loging_id' => 'user2']);
    
    // Create a transaction log
    $log = new TransactionLog();
    $log->from_user = 'user1';
    $log->to_user = 'user2';
    $log->amount = 100;
    $log->save();
});
```

If any operation fails, all changes are rolled back.

### 8.6 Complete CRUD example

```php
<?php
// Create
$course = new Course();
$course->name = 'PHP Basics';
$course->instructor_id = 'john_doe';
$course->description = 'Learn PHP fundamentals';
$course->save();

// Read
$course = Course::findOne(['id' => 1]);
echo $course->name;  // PHP Basics

// Update
$course->description = 'Learn PHP and MySQL';
$course->update(['id' => 1]);

// Delete
$course->delete();

// Query
$johnsCourses = Course::where('instructor_id', '=', 'john_doe');
```

### 8.7 Supported databases

The framework includes support for multiple database engines:

- **MySQL** - `core/db/MySqlDBModel.php`
- **PostgreSQL** - `core/db/PostgreDBModel.php`
- **SQL Server** - `core/db/MSSQLServerDBModel.php`
- **Oracle** - `core/db/OracleDBModel.php`

Database selection is configured in `public/config.php`:

```php
$config = [
    'db' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=mandakini',
        'username' => 'root',
        'password' => 'password',
    ]
];
```

The DSN (Data Source Name) determines which database engine is used.

## 9. Views and Layouts

Views are stored in `views/` and follow the naming pattern:

- `homeView.php`
- `loginView.php`
- `profileView.php`

The controller returns a view through `render()`.

```php
return $this->render('login', ['model' => $model]);
```

This loads `views/loginView.php`.

### 9.1 Layouts - Reusable page structure

The project uses layouts stored in `views/layout/` to avoid repeating HTML structure:

Examples:

- `main.php` - Default layout for regular pages
- `auth.php` - Layout for login/register pages (no navigation)
- `mandakini_layout.php` - Custom layout for Mandakini pages

The layout includes a placeholder where page content is inserted:

```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->title ?? 'Mandakini'; ?></title>
    <link rel="stylesheet" href="/css/mandakini.css">
</head>
<body>
    <nav><?php // Navigation here ?></nav>
    
    <main>
        {{content}}  <!-- Page content goes here -->
    </main>
    
    <footer><?php // Footer here ?></footer>
</body>
</html>
```

### 9.2 Basic view example - homeView.php

```php
<div class="home-container">
    <h1>Welcome to Mandakini</h1>
    <p>This is the home page.</p>
    
    <div class="features">
        <h2>Our Features</h2>
        <ul>
            <li>Fast Development</li>
            <li>Easy to Learn</li>
            <li>Built-in Authentication</li>
        </ul>
    </div>
</div>
```

### 9.3 View with variables - profileView.php

You can pass variables into the view from the controller:

```php
// In Controller:
return $this->render('profile', [
    'user' => $user,
    'courses' => $courses,
    'title' => 'User Profile',
]);
```

Inside the view, these variables are available as regular PHP variables:

```php
<!-- views/profileView.php -->
<div class="profile-container">
    <h1><?php echo $user->getDisplayName(); ?>'s Profile</h1>
    
    <div class="user-info">
        <p><strong>Email:</strong> <?php echo $user->email; ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($user->category); ?></p>
        <p><strong>Member Since:</strong> <?php echo $user->created_at; ?></p>
    </div>
    
    <div class="user-courses">
        <h2>My Courses</h2>
        <?php if (count($courses) > 0): ?>
            <ul>
                <?php foreach ($courses as $course): ?>
                    <li>
                        <a href="/course/<?php echo $course->id; ?>">
                            <?php echo $course->name; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No courses yet.</p>
        <?php endif; ?>
    </div>
</div>
```

### 9.4 View with conditional rendering

```php
<!-- views/dashboardView.php -->
<div class="dashboard">
    <h1>Dashboard</h1>
    
    <?php if (Application::$app->user->category === 'admin'): ?>
        <div class="admin-panel">
            <h2>Admin Panel</h2>
            <a href="/admin/users">Manage Users</a>
            <a href="/admin/courses">Manage Courses</a>
        </div>
    <?php endif; ?>
    
    <?php if (Application::$app->user->category === 'instructor'): ?>
        <div class="instructor-panel">
            <h2>Instructor Panel</h2>
            <a href="/instructor/create-course">Create Course</a>
            <a href="/instructor/my-courses">My Courses</a>
        </div>
    <?php endif; ?>
    
    <?php if (Application::$app->user->category === 'student'): ?>
        <div class="student-panel">
            <h2>Student Dashboard</h2>
            <a href="/courses">Browse Courses</a>
            <a href="/my-courses">My Courses</a>
        </div>
    <?php endif; ?>
</div>
```

### 9.5 View with flash messages

Flash messages show notifications from the previous request:

```php
<!-- views/layout/main.php -->
<body>
    <nav><?php // Navigation ?></nav>
    
    <!-- Flash Message Display -->
    <?php if (Application::$app->session->getFlash('success')): ?>
        <div class="alert alert-success">
            <?php echo Application::$app->session->getFlash('success'); ?>
        </div>
    <?php endif; ?>
    
    <?php if (Application::$app->session->getFlash('error')): ?>
        <div class="alert alert-danger">
            <?php echo Application::$app->session->getFlash('error'); ?>
        </div>
    <?php endif; ?>
    
    <main>
        {{content}}
    </main>
</body>
```

In the controller, set flash messages:

```php
public function registerAction() {
    // ... registration code ...
    Application::$app->session->setFlash('success', 'Registration successful! Please log in.');
    Application::$app->response->redirect('/login');
}
```

## 10. Form Builder

The form helper is in `core/form` and is designed to reduce repetitive HTML creation.

### 10.1 Basic form example - Login form

```php
<!-- views/loginView.php -->
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <div class="form-group">
        <?php echo $form->field($model, 'loging_id')->emailField() ?>
    </div>
    
    <div class="form-group">
        <?php echo $form->field($model, 'password')->passwordField() ?>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="/register">Don't have an account?</a>
    </div>
<?php \app\core\form\Form::end() ?>
```

This generates:

```html
<form method="post">
    <div class="form-group">
        <label for="loging_id">Login ID</label>
        <input type="email" name="loging_id" id="loging_id" value="" required>
        <!-- Error message if validation fails -->
    </div>
    <!-- ... other fields ... -->
    <button type="submit">Login</button>
</form>
```

### 10.2 Complete registration form example

```php
<!-- views/registerView.php -->
<div class="register-container">
    <h1>Create Account</h1>
    
    <?php $form = \app\core\form\Form::begin('', 'post') ?>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $form->field($model, 'firstName')->textField() ?>
            </div>
            
            <div class="form-group col-md-6">
                <?php echo $form->field($model, 'lastName')->textField() ?>
            </div>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'loging_id')->textField() ?>
            <small>Username for login (3-20 characters)</small>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'email')->emailField() ?>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'password')->passwordField() ?>
            <small>At least 8 characters</small>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'confirmPassword')->passwordField() ?>
        </div>
        
        <div class="form-group checkbox">
            <label>
                <input type="checkbox" name="agree" required>
                I agree to the Terms of Service
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        <p class="text-center">
            Already have an account? <a href="/login">Log in here</a>
        </p>
        
    <?php \app\core\form\Form::end() ?>
</div>
```

### 10.3 All supported field types

```php
<!-- Text input -->
<?php echo $form->field($model, 'firstName')->textField() ?>

<!-- Email input -->
<?php echo $form->field($model, 'email')->emailField() ?>

<!-- Password input -->
<?php echo $form->field($model, 'password')->passwordField() ?>

<!-- Numeric input -->
<?php echo $form->field($model, 'age')->numberField() ?>

<!-- URL input -->
<?php echo $form->field($model, 'website')->urlField() ?>

<!-- Telephone input -->
<?php echo $form->field($model, 'phone')->telField() ?>

<!-- Date input -->
<?php echo $form->field($model, 'birth_date')->dateField() ?>

<!-- Time input -->
<?php echo $form->field($model, 'appointment_time')->timeField() ?>

<!-- Search input -->
<?php echo $form->field($model, 'search_query')->searchField() ?>

<!-- Hidden input (for CSRF tokens, etc.) -->
<?php echo $form->field($model, 'token')->hiddenField() ?>

<!-- Textarea -->
<?php echo $form->field($model, 'bio')->textareaField() ?>

<!-- Select dropdown -->
<?php echo $form->field($model, 'category')->selectField([
    'student' => 'Student',
    'instructor' => 'Instructor',
    'admin' => 'Administrator',
]) ?>

<!-- Radio buttons -->
<?php echo $form->field($model, 'gender')->radioField([
    'male' => 'Male',
    'female' => 'Female',
    'other' => 'Other',
]) ?>

<!-- Checkbox -->
<?php echo $form->field($model, 'subscribe')->checkboxField() ?>

<!-- File upload -->
<?php echo $form->field($model, 'profile_picture')->fileField() ?>
```

### 10.4 Select field with database values

```php
<?php 
    // Get all categories from database
    $categories = Category::findAll();
    $categoryOptions = [];
    foreach ($categories as $category) {
        $categoryOptions[$category->id] = $category->name;
    }
?>

<?php echo $form->field($model, 'category_id')->selectField($categoryOptions) ?>
```

### 10.5 Multi-select (checkboxes for multiple selections)

```php
<?php 
    $interests = Interest::findAll();
    $interestOptions = [];
    foreach ($interests as $interest) {
        $interestOptions[$interest->id] = $interest->name;
    }
?>

<label>Select your interests:</label>
<?php foreach ($interestOptions as $id => $name): ?>
    <div class="form-check">
        <input type="checkbox" name="interests[]" value="<?php echo $id; ?>" 
               id="interest_<?php echo $id; ?>">
        <label for="interest_<?php echo $id; ?>"><?php echo $name; ?></label>
    </div>
<?php endforeach; ?>
```

### 10.6 File upload field

```php
<!-- views/uploadView.php -->
<?php $form = \app\core\form\Form::begin('', 'post', ['enctype' => 'multipart/form-data']) ?>
    
    <div class="form-group">
        <label for="file">Upload Document</label>
        <?php echo $form->field($model, 'document')->fileField() ?>
        <small>Allowed: PDF, DOC, DOCX (Max 5MB)</small>
    </div>
    
    <button type="submit">Upload</button>
    
<?php \app\core\form\Form::end() ?>
```

In the controller:

```php
public function uploadAction() {
    if (Application::$app->request->isPost()) {
        // Access uploaded file from $_FILES
        if (isset($_FILES['document'])) {
            $file = $_FILES['document'];
            
            // Validate file
            $allowed = ['pdf', 'doc', 'docx'];
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            if (in_array(strtolower($fileExt), $allowed)) {
                // Save file
                $uploadDir = '/storage/documents/';
                $newFileName = uniqid() . '.' . $fileExt;
                move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName);
                
                echo "File uploaded successfully";
            }
        }
    }
}
```

### 10.7 Advanced form example - Course creation form

```php
<!-- views/createCourseView.php -->
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    
    <fieldset>
        <legend>Basic Information</legend>
        
        <div class="form-group">
            <?php echo $form->field($model, 'name')->textField() ?>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'description')->textareaField() ?>
        </div>
    </fieldset>
    
    <fieldset>
        <legend>Course Settings</legend>
        
        <div class="form-group">
            <?php echo $form->field($model, 'category_id')->selectField($categoryOptions) ?>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'level')->radioField([
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
            ]) ?>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'price')->numberField() ?>
        </div>
    </fieldset>
    
    <button type="submit" class="btn btn-primary">Create Course</button>
    <a href="/courses" class="btn btn-secondary">Cancel</a>
    
<?php \app\core\form\Form::end() ?>
```

## 11. Authentication and Session Management

Mandakini includes a session-based user system with login/logout capabilities.

### 11.1 Understanding the login flow

Here's what happens when a user logs in:

```
1. User enters login ID and password in the form
2. Controller validates the input
3. Database lookup finds the user by login ID
4. Password verification using password_verify()
5. If both are correct, Application::$app->login($user) is called
6. User ID is stored in session
7. Redirect to dashboard/profile
```

### 11.2 Complete authentication controller

```php
<?php
namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\User;
use app\models\LoginForm;
use app\models\RegisterForm;

class AuthController extends Controller {
    
    /**
     * Show login form and process login
     */
    public function loginAction() {
        // If already logged in, redirect to profile
        if (!Application::isGuest()) {
            Application::$app->response->redirect('/profile');
            return '';
        }
        
        $model = new LoginForm();
        
        // Check if form was submitted
        if (Application::$app->request->isPost()) {
            // Load form data
            $model->loadData(Application::$app->request->getBody());
            
            // Validate input
            if ($model->validate()) {
                // Find user in database
                $user = User::findOne(['loging_id' => $model->loging_id]);
                
                // Verify password
                if ($user && password_verify($model->password, $user->password)) {
                    // Login successful - store user in session
                    Application::$app->login($user);
                    
                    // Set success message
                    Application::$app->session->setFlash('success', 
                        'Welcome back, ' . $user->firstName . '!');
                    
                    // Redirect to profile
                    Application::$app->response->redirect('/profile');
                    return '';
                } else {
                    // Login failed
                    $model->addError('loging_id', 'Invalid username or password');
                }
            }
        }
        
        // Show login form
        $this->setLayout('auth');
        return $this->render('login', ['model' => $model]);
    }
    
    /**
     * Show registration form and process registration
     */
    public function registerAction() {
        $model = new RegisterForm();
        
        if (Application::$app->request->isPost()) {
            $model->loadData(Application::$app->request->getBody());
            
            if ($model->validate()) {
                // Create new user
                $user = new User();
                $user->loging_id = $model->loging_id;
                $user->email = $model->email;
                $user->firstName = $model->firstName;
                $user->lastName = $model->lastName;
                // Hash password before saving
                $user->password = password_hash($model->password, PASSWORD_DEFAULT);
                $user->category = 'student';  // Default role
                
                if ($user->save()) {
                    // Automatically log in the new user
                    Application::$app->login($user);
                    
                    Application::$app->session->setFlash('success', 
                        'Registration successful! Welcome to Mandakini!');
                    
                    Application::$app->response->redirect('/profile');
                    return '';
                } else {
                    // Database save failed
                    $model->addError('loging_id', 'Could not create account');
                }
            }
        }
        
        $this->setLayout('auth');
        return $this->render('register', ['model' => $model]);
    }
    
    /**
     * Logout the current user
     */
    public function logoutAction() {
        Application::$app->logout();
        
        Application::$app->session->setFlash('success', 'You have been logged out');
        
        Application::$app->response->redirect('/login');
        return '';
    }
    
    /**
     * Show profile page (protected - requires login)
     */
    public function profileAction() {
        // Protect with authentication middleware
        $this->setMiddleware(new \app\core\middlewares\AuthMiddleware([]));
        
        // Get current user from session
        $user = Application::$app->user;
        
        return $this->render('profile', [
            'user' => $user,
            'title' => $user->getDisplayName(),
        ]);
    }
}
```

### 11.3 Using authentication in the session

```php
// Check if user is logged in
if (Application::isGuest()) {
    echo "Not logged in";
} else {
    echo "Logged in as: " . Application::$app->user->firstName;
}

// Get current user
$currentUser = Application::$app->user;
echo $currentUser->email;
echo $currentUser->category;

// Check user role
if (Application::$app->user->category === 'admin') {
    echo "Admin options available";
}
```

### 11.4 Session management - Flash messages

Flash messages display once then disappear:

```php
// Set flash message in controller
Application::$app->session->setFlash('success', 'Profile updated!');
Application::$app->session->setFlash('error', 'Email already exists');

// Display in view
<?php if (Application::$app->session->getFlash('success')): ?>
    <div class="alert alert-success">
        <?php echo Application::$app->session->getFlash('success'); ?>
    </div>
<?php endif; ?>

<?php if (Application::$app->session->getFlash('error')): ?>
    <div class="alert alert-danger">
        <?php echo Application::$app->session->getFlash('error'); ?>
    </div>
<?php endif; ?>
```

### 11.5 Logout flow

```php
public function logoutAction() {
    // Clear session and remove user
    Application::$app->logout();
    
    // Redirect to home or login
    Application::$app->response->redirect('/');
    return '';
}
```

### 11.6 Guest checks and redirects

Middleware automatically checks if user is logged in:

```php
public function profileAction() {
    // This sets up auth check
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware([]));
    
    // If user is not logged in, middleware redirects to login
    // This code only runs if logged in
    $user = Application::$app->user;
    
    return $this->render('profile', ['user' => $user]);
}
```

### 11.7 Role-based access control

```php
public function adminPanelAction() {
    // Only allow admin users
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['admin']));
    
    // Only admins can reach here
    $users = User::findAll();
    return $this->render('admin', ['users' => $users]);
}

public function instructorDashboardAction() {
    // Allow both instructors and admins
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['instructor', 'admin']));
    
    // Get current user's courses
    $courses = Course::where('instructor_id', '=', Application::$app->user->loging_id);
    return $this->render('instructor_dashboard', ['courses' => $courses]);
}

public function studentCoursesAction() {
    // Only students and admins
    $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['student', 'admin']));
    
    // Show student's enrolled courses
    return $this->render('my_courses');
}
```

### 11.8 Hashing passwords safely

Never store passwords in plain text. Always hash them:

```php
// When registering
$password = 'userPassword123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Store $hashedPassword in database
$user->password = $hashedPassword;
$user->save();

// When logging in, verify
$inputPassword = 'userPassword123';
$storedHash = $user->password;

if (password_verify($inputPassword, $storedHash)) {
    // Password matches
    Application::$app->login($user);
} else {
    // Password incorrect
    echo "Invalid password";
}
```

## 12. Middleware

Middleware is used to authorize and gate actions before they are executed.

### 12.1 Understanding middleware

Middleware runs BEFORE your controller action. It can:

- Check if user is logged in
- Check if user has required role/permission
- Block unauthorized access
- Redirect to login or error page

If middleware check fails, the controller action never runs.

### 12.2 Base middleware

All middleware extends `core/middlewares/BaseMiddleware.php`:

```php
<?php
namespace app\core\middlewares;

abstract class BaseMiddleware {
    abstract public function execute();
}
```

### 12.3 Auth middleware - How it works

`AuthMiddleware` checks whether a user is allowed to access a route or action:

```php
<?php
namespace app\core\middlewares;

use app\core\Application;

class AuthMiddleware extends BaseMiddleware {
    private array $allowedRoles;
    
    public function __construct(array $allowedRoles = []) {
        // Empty array means any logged-in user is allowed
        // ['admin'] means only admin users
        // ['admin', 'instructor'] means admins and instructors
        $this->allowedRoles = $allowedRoles;
    }
    
    public function execute() {
        // Check if user is logged in
        if (Application::isGuest()) {
            // Not logged in - redirect to login
            Application::$app->response->redirect('/login');
            return false;
        }
        
        // User is logged in. Check role if specified
        if (!empty($this->allowedRoles)) {
            $userRole = Application::$app->user->category;
            
            if (!in_array($userRole, $this->allowedRoles)) {
                // User role not allowed
                throw new ForbiddenException();  // Show 403 error
            }
        }
        
        return true;
    }
}
```

### 12.4 Protection patterns - Using middleware

**Pattern 1: Require login, any role**

```php
public function profileAction() {
    // Any logged-in user can access
    $this->setMiddleware(new AuthMiddleware([]));
    
    $user = Application::$app->user;
    return $this->render('profile', ['user' => $user]);
}
```

**Pattern 2: Require specific role**

```php
public function adminPanel() {
    // Only admins can access
    $this->setMiddleware(new AuthMiddleware(['admin']));
    
    $users = User::findAll();
    return $this->render('admin', ['users' => $users]);
}
```

**Pattern 3: Multiple allowed roles**

```php
public function instructorDashboard() {
    // Instructors and admins can access
    $this->setMiddleware(new AuthMiddleware(['instructor', 'admin']));
    
    return $this->render('instructor_dashboard');
}
```

**Pattern 4: No middleware (public page)**

```php
public function homeAction() {
    // No middleware - anyone can access
    return $this->render('home');
}
```

### 12.5 Complete middleware example in controllers

```php
<?php
namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\middlewares\AuthMiddleware;
use app\models\User;

class AdminController extends Controller {
    
    // Admin-only dashboard
    public function dashboardAction() {
        $this->setMiddleware(new AuthMiddleware(['admin']));
        
        $stats = [
            'total_users' => count(User::findAll()),
            'pending_contacts' => count(Contact::where('status', '=', 'new')),
            'total_courses' => count(Course::findAll()),
        ];
        
        return $this->render('admin_dashboard', $stats);
    }
    
    // Admin user management
    public function usersAction() {
        $this->setMiddleware(new AuthMiddleware(['admin']));
        
        $users = User::findAll();
        return $this->render('admin_users', ['users' => $users]);
    }
    
    // Edit user (admin or instructor can edit students)
    public function editUserAction() {
        // Instructors can edit students, admins can edit anyone
        $this->setMiddleware(new AuthMiddleware(['admin', 'instructor']));
        
        $userId = $_GET['id'] ?? null;
        $user = User::findOne(['loging_id' => $userId]);
        
        // Additional permission check
        if (!Application::$app->user->role === 'admin' && 
            $user->category !== 'student') {
            throw new ForbiddenException();
        }
        
        // ... rest of logic
    }
}

class CourseController extends Controller {
    
    // Public - list all courses
    public function listAction() {
        // No middleware - public page
        $courses = Course::findAll();
        return $this->render('courses', ['courses' => $courses]);
    }
    
    // Student - enroll in course
    public function enrollAction() {
        // Only students can enroll
        $this->setMiddleware(new AuthMiddleware(['student']));
        
        $courseId = $_POST['course_id'];
        $course = Course::findOne(['id' => $courseId]);
        
        // Create enrollment
        $enrollment = new Enrollment();
        $enrollment->student_id = Application::$app->user->loging_id;
        $enrollment->course_id = $courseId;
        $enrollment->save();
        
        Application::$app->session->setFlash('success', 'Enrolled in ' . $course->name);
        Application::$app->response->redirect('/courses');
    }
    
    // Instructor - create course
    public function createAction() {
        // Only instructors can create
        $this->setMiddleware(new AuthMiddleware(['instructor', 'admin']));
        
        // Show course creation form
        return $this->render('create_course');
    }
}

class ProfileController extends Controller {
    
    // All logged-in users can view profiles
    public function viewAction() {
        $this->setMiddleware(new AuthMiddleware([]));
        
        $username = $_GET['user'];
        $user = User::findOne(['loging_id' => $username]);
        
        return $this->render('public_profile', ['user' => $user]);
    }
    
    // Only your own profile can be edited
    public function editAction() {
        $this->setMiddleware(new AuthMiddleware([]));
        
        $currentUser = Application::$app->user;
        
        // Make sure user is editing their own profile
        $targetUsername = $_GET['user'] ?? null;
        if ($targetUsername !== $currentUser->loging_id && 
            $currentUser->category !== 'admin') {
            throw new ForbiddenException();
        }
        
        // ... rest of logic
    }
}
```

### 12.6 Common middleware errors and solutions

| Error | Cause | Solution |
|-------|-------|----------|
| 404 Page Not Found | Action doesn't exist or middleware redirects | Check controller action name has `Action` suffix |
| 403 Forbidden | Role not allowed | Check user role, verify middleware array |
| Redirect loop | Middleware redirect URL has same middleware | Use different URL for redirect |
| Action runs when shouldn't | Middleware not set | Call `$this->setMiddleware()` in action |

### 12.7 Why middleware matters

Middleware helps with:

- Login required pages - block non-authenticated users
- Role-based access - block users without required role
- Protected admin actions - ensure only admins can access
- Feature restrictions - limit features by user type
- Permission checks - centralized access control

## 13. Request and Response Lifecycle

A standard request flow in Mandakini looks like this:

### 13.1 Complete request lifecycle

```
1. Browser sends HTTP request
   Example: GET /course/5

2. Server receives request in public/index.php
   - Loads Composer autoloader
   - Creates Application instance
   - Loads routes from public/routes.php

3. Request object reads the request
   - Gets path: /course/5
   - Gets method: GET
   - Extracts parameters: {id} = 5

4. Router finds matching route
   - Looks for route matching /course/{id}
   - Finds: $app->router->get('/course/{id}', [CourseController::class, 'view'])

5. Load and instantiate controller
   - Creates CourseController instance
   - Calls viewAction() method
   - Passes parameters via $_GET

6. Middleware checks permission
   - If middleware is set, it runs first
   - Checks if user is logged in
   - Checks if user has required role

7. Controller action runs
   - Processes the request
   - May fetch data from database
   - May validate forms
   - Prepares data for view

8. View renders HTML
   - Calls $this->render('course', ['course' => $course])
   - Loads views/courseView.php
   - Wraps in layout (main.php)
   - Replaces {{content}} with view HTML

9. Response sends output
   - HTTP response headers set
   - HTML sent to browser

10. Browser displays page
    - Renders HTML
    - Shows user the page
```

### 13.2 Request lifecycle example - Real scenario

```
User visits: http://mandakini.local/course/5

Step 1 - Entry Point (public/index.php)
├── Load autoloader
├── Start session
├── Create Application
├── Include routes.php
└── Call $app->run()

Step 2 - Routing
├── Request: /course/5
├── Search routes for match
└── Find: $app->router->get('/course/{id}', [CourseController::class, 'view'])

Step 3 - Extract Parameters
├── URL: /course/5
├── Pattern: /course/{id}
└── Parameters: [id => 5]

Step 4 - Load Controller
├── Class: app\controllers\CourseController
└── Method: viewAction()

Step 5 - Execute Middleware (if any)
├── Check: Is user logged in?
├── Check: Does user have permission?
└── Allow or Redirect

Step 6 - Execute Controller Action
```php
public function viewAction() {
    $courseId = $_GET['id'];
    $course = Course::findOne(['id' => $courseId]);
    return $this->render('course', ['course' => $course]);
}
```

Step 7 - Render View
├── Load: views/courseView.php
├── Pass data: ['course' => $course]
└── Get HTML output

Step 8 - Apply Layout
├── Load layout: views/layout/main.php
├── Find {{content}} placeholder
└── Insert view HTML

Step 9 - Send Response
├── Set HTTP headers
├── Send HTML to browser
└── Request complete

Step 10 - Browser displays page
```

### 13.3 Handling different HTTP methods

**GET request example:**
```php
// User visits: /profile
public function profileAction() {
    // Show profile page
    $user = Application::$app->user;
    return $this->render('profile', ['user' => $user]);
}
```

**POST request example:**
```php
// User submits form to /profile
public function profileAction() {
    if (Application::$app->request->isPost()) {
        // Handle form submission
        $model = new ProfileForm();
        $model->loadData(Application::$app->request->getBody());
        
        if ($model->validate()) {
            // Save changes
            Application::$app->session->setFlash('success', 'Profile updated!');
            Application::$app->response->redirect('/profile');
        }
    } else {
        // Show form (GET request)
        $user = Application::$app->user;
        return $this->render('profile', ['user' => $user]);
    }
}
```

### 13.4 Request object deep dive

The Request object handles reading the incoming request:

```php
<?php
// In controller
$request = Application::$app->request;

// Get the URL path
$path = $request->getPath();  // '/course/5'

// Get the HTTP method
$method = $request->method();  // 'GET' or 'POST'

// Check method type
if ($request->isGet()) {
    // Handle GET request
}

if ($request->isPost()) {
    // Handle POST request
}

// Get form data (POST or GET)
$body = $request->getBody();
$email = $body['email'] ?? null;

// All $_GET parameters
$_GET['id']  // 5

// All $_POST parameters
$_POST['email']  // 'user@example.com'
```

### 13.5 Response object deep dive

The Response object handles sending responses:

```php
<?php
$response = Application::$app->response;

// Redirect to another page
$response->redirect('/profile');  // 301 redirect

// Set HTTP status code
$response->setStatusCode(404);    // 404 Not Found
$response->setStatusCode(200);    // 200 OK
$response->setStatusCode(403);    // 403 Forbidden

// Return response from controller
return '';  // Ends execution
```

### 13.6 Error handling in the lifecycle

**404 Not Found:**
```
User visits: /invalid-page
↓
Router searches all routes
↓
No matching route found
↓
Throw NotFoundException()
↓
Framework catches exception
↓
Show 404 error view (_404View.php)
```

**403 Forbidden:**
```
User visits: /admin (not authorized)
↓
Route found
↓
Middleware checks permission
↓
User lacks required role
↓
Throw ForbiddenException()
↓
Framework shows 403 error
```

**500 Server Error:**
```
Controller throws exception
↓
Database error or runtime error
↓
Framework catches error
↓
If debug=true: Show error with stack trace
If debug=false: Show generic error page (_errorView.php)
```

## 14. Routing Deep Dive

Routes define which controller action handles each URL. Routes are defined in `public/routes.php`.

### 14.1 Static routes - Fixed URLs

```php
// GET request to /about shows the about page
$app->router->get('/about', [SiteController::class, 'about']);

// GET request to /contact shows the contact page
$app->router->get('/contact', [SiteController::class, 'contact']);

// GET request to /terms shows the terms page
$app->router->get('/terms', [SiteController::class, 'terms']);
```

When a user visits `/about`, the router finds the matching route and calls `SiteController->aboutAction()`.

### 14.2 POST routes - Form submissions

```php
// GET request shows the login form
$app->router->get('/login', [AuthController::class, 'login']);

// POST request processes the form submission
$app->router->post('/login', [AuthController::class, 'login']);

// GET shows the registration form
$app->router->get('/register', [AuthController::class, 'register']);

// POST processes the registration
$app->router->post('/register', [AuthController::class, 'register']);

// POST handles contact form submission
$app->router->post('/contact', [SiteController::class, 'contact']);
```

The same controller action handles both GET (showing form) and POST (processing form).

### 14.3 Dynamic route parameters - Resource URLs

Parameters in curly braces `{paramName}` are captured from the URL:

```php
// /course/1 -> {id} = 1
// /course/php-basics -> {id} = php-basics
$app->router->get('/course/{id}', [SiteController::class, 'course']);

// /user/john_doe -> {username} = john_doe
$app->router->get('/user/{username}', [ProfileController::class, 'profile']);

// /post/123/edit -> {id} = 123
$app->router->get('/post/{id}/edit', [PostController::class, 'edit']);

// Multiple parameters
// /course/5/lesson/12 -> {courseId} = 5, {lessonId} = 12
$app->router->get('/course/{courseId}/lesson/{lessonId}', [LessonController::class, 'view']);
```

### 14.4 URL parameter access in controllers

The router automatically extracts URL parameters and makes them available:

```php
public function courseAction() {
    // Get the {id} parameter from the URL
    $courseId = Application::$app->request->getBody()['id'] ?? null;
    // Or from $_GET
    $courseId = $_GET['id'] ?? null;
    
    // Fetch the course
    $course = Course::findOne(['id' => $courseId]);
    
    if (!$course) {
        throw new NotFoundException();
    }
    
    return $this->render('course', ['course' => $course]);
}
```

### 14.5 Complete routing example

```php
<?php
// File: public/routes.php

// Home and static pages
$app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/about', [SiteController::class, 'about']);
$app->router->get('/contact', [SiteController::class, 'contact']);

// Authentication routes
$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);
$app->router->post('/logout', [AuthController::class, 'logout']);

// Profile and user routes
$app->router->get('/profile', [AuthController::class, 'profile']);
$app->router->get('/user/{username}', [ProfileController::class, 'profile']);
$app->router->get('/profile/edit', [AuthController::class, 'editProfile']);
$app->router->post('/profile/edit', [AuthController::class, 'editProfile']);

// Course routes
$app->router->get('/courses', [CourseController::class, 'list']);
$app->router->get('/course/{id}', [CourseController::class, 'view']);
$app->router->get('/course/{id}/lessons', [LessonController::class, 'index']);
$app->router->get('/course/{courseId}/lesson/{lessonId}', [LessonController::class, 'view']);

// Admin routes
$app->router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$app->router->get('/admin/users', [AdminController::class, 'users']);
$app->router->get('/admin/user/{id}/edit', [AdminController::class, 'editUser']);
$app->router->post('/admin/user/{id}/edit', [AdminController::class, 'editUser']);

// API routes (if needed)
$app->router->post('/api/enrollment', [ApiController::class, 'enrollCourse']);
$app->router->delete('/api/enrollment/{enrollmentId}', [ApiController::class, 'unenrollCourse']);
```

### 14.6 REST-like routing patterns

While Mandakini is mainly MVC, you can design routes to follow REST conventions:

```php
// List all courses (index)
$app->router->get('/courses', [CourseController::class, 'index']);

// Show create form
$app->router->get('/courses/create', [CourseController::class, 'create']);

// Handle POST create
$app->router->post('/courses', [CourseController::class, 'store']);

// Show single course (show)
$app->router->get('/course/{id}', [CourseController::class, 'show']);

// Show edit form
$app->router->get('/course/{id}/edit', [CourseController::class, 'edit']);

// Handle POST update
$app->router->post('/course/{id}', [CourseController::class, 'update']);

// Handle delete
$app->router->post('/course/{id}/delete', [CourseController::class, 'destroy']);
```

### 14.7 Redirect routes

Redirect one URL to another:

```php
$app->router->get('/old-page', function() {
    Application::$app->response->redirect('/new-page');
});
```

### 14.8 Route matching rules

- Routes are matched in the order they are defined
- More specific routes should come before general ones
- Parameters must match the format (alphanumeric by default)

## 15. Configuration

The application configuration is in `public/config.php`. This file sets up the app's behavior, database connection, and user model.

### 15.1 Basic configuration example

```php
<?php
// public/config.php

$config = [
    // User model class - used for login/session
    'userClass' => \app\models\User::class,
    
    // Application name - used in views
    'appName' => 'Mandakini Learning Platform',
    
    // Debug mode - shows detailed errors
    'debug' => true,
    
    // Database connection settings
    'db' => [
        // Data Source Name - determines database type and location
        'dsn' => 'mysql:host=localhost;port=3306;dbname=mandakini',
        
        // Database username
        'username' => 'root',
        
        // Database password
        'password' => '',
    ]
];

return $config;
```

### 15.2 Database configuration for different systems

**MySQL (localhost)**
```php
'db' => [
    'dsn' => 'mysql:host=localhost;port=3306;dbname=mandakini',
    'username' => 'root',
    'password' => '',
]
```

**MySQL (remote server)**
```php
'db' => [
    'dsn' => 'mysql:host=db.example.com;port=3306;dbname=mandakini',
    'username' => 'db_user',
    'password' => 'secure_password_123',
]
```

**PostgreSQL**
```php
'db' => [
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=mandakini',
    'username' => 'postgres',
    'password' => 'postgres_password',
]
```

**SQL Server**
```php
'db' => [
    'dsn' => 'sqlsrv:Server=localhost;Database=mandakini',
    'username' => 'sa',
    'password' => 'sa_password',
]
```

**Oracle**
```php
'db' => [
    'dsn' => 'oci:dbname=mandakini',
    'username' => 'oracle_user',
    'password' => 'oracle_password',
]
```

### 15.3 Development vs Production configuration

**Development setup** (local machine):
```php
$config = [
    'userClass' => \app\models\User::class,
    'appName' => 'Mandakini Dev',
    'debug' => true,  // Show all errors
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=mandakini_dev',
        'username' => 'root',
        'password' => '',
    ]
];
```

**Production setup** (live server):
```php
$config = [
    'userClass' => \app\models\User::class,
    'appName' => 'Mandakini',
    'debug' => false,  // Hide errors from users
    'db' => [
        'dsn' => 'mysql:host=db-prod.company.com;dbname=mandakini_prod',
        'username' => 'prod_user',
        'password' => 'very_secure_password_xyz',
    ]
];
```

### 15.4 Environment-based configuration

To use different configs for different environments:

```php
<?php
// public/config.php

// Detect environment
$env = getenv('APP_ENV') ?: 'development';

if ($env === 'production') {
    $config = [
        'userClass' => \app\models\User::class,
        'appName' => 'Mandakini',
        'debug' => false,
        'db' => [
            'dsn' => getenv('DB_DSN'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
        ]
    ];
} else {
    $config = [
        'userClass' => \app\models\User::class,
        'appName' => 'Mandakini Dev',
        'debug' => true,
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=mandakini',
            'username' => 'root',
            'password' => '',
        ]
    ];
}

return $config;
```

### 15.5 Configuration with environment variables

Use a `.env` file for sensitive data:

```bash
# .env (in root directory)
DB_DSN=mysql:host=localhost;dbname=mandakini
DB_USER=root
DB_PASS=
APP_NAME=Mandakini
APP_DEBUG=true
```

Then load it in config:

```php
<?php
// Load .env file if it exists
if (file_exists(dirname(__DIR__) . '/.env')) {
    $env_file = file(dirname(__DIR__) . '/.env');
    foreach ($env_file as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$config = [
    'userClass' => \app\models\User::class,
    'appName' => getenv('APP_NAME') ?: 'Mandakini',
    'debug' => getenv('APP_DEBUG') === 'true',
    'db' => [
        'dsn' => getenv('DB_DSN'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
    ]
];

return $config;
```

### 15.6 Important configuration keys reference

| Key | Type | Purpose | Example |
|-----|------|---------|---------|
| `userClass` | Class | Which model handles user login | `\app\models\User::class` |
| `appName` | String | Application display name | `'Mandakini'` |
| `debug` | Boolean | Show detailed errors | `true` (dev) / `false` (prod) |
| `db.dsn` | String | Database connection string | `'mysql:host=localhost;dbname=app'` |
| `db.username` | String | Database user | `'root'` |
| `db.password` | String | Database password | `'password'` |

## 16. Database Migrations

Migrations manage database schema changes in a version-controlled way. They're stored in `migrations/` directory.

### 16.1 What are migrations?

Migrations are PHP files that describe database changes:

```php
<?php
// migrations/m0001_initials.php

class m0001_initials {
    
    public function up() {
        // Code to create tables/columns
    }
    
    public function down() {
        // Code to undo - drop tables/columns
    }
}
```

#### Running migrations:

```php
// Run from terminal
php migrations.php

// Or from code
echo $app->db->applyMigrations();
```

### 16.2 Example migration - Create users table

```php
<?php
// migrations/m0001_create_users_table.php

class m0001_create_users_table {
    
    public function up() {
        $sql = "CREATE TABLE users (
            loging_id VARCHAR(255) PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            firstName VARCHAR(255) NOT NULL,
            lastName VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            category VARCHAR(50) DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Created users table\n";
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS users";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Dropped users table\n";
    }
}
```

### 16.3 Example migration - Create courses table

```php
<?php
// migrations/m0002_create_courses_table.php

class m0002_create_courses_table {
    
    public function up() {
        $sql = "CREATE TABLE courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            instructor_id VARCHAR(255) NOT NULL,
            category_id INT,
            price DECIMAL(10, 2) DEFAULT 0,
            level VARCHAR(50) DEFAULT 'beginner',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (instructor_id) REFERENCES users(loging_id)
        )";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Created courses table\n";
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS courses";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Dropped courses table\n";
    }
}
```

### 16.4 Example migration - Add column to existing table

```php
<?php
// migrations/m0003_add_bio_to_users.php

class m0003_add_bio_to_users {
    
    public function up() {
        $sql = "ALTER TABLE users ADD COLUMN bio TEXT NULL";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Added bio column to users table\n";
    }
    
    public function down() {
        $sql = "ALTER TABLE users DROP COLUMN bio";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Removed bio column from users table\n";
    }
}
```

### 16.5 Example migration - Create contacts/inquiries table

```php
<?php
// migrations/m0004_create_contacts_table.php

class m0004_create_contacts_table {
    
    public function up() {
        $sql = "CREATE TABLE contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Created contacts table\n";
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS contacts";
        
        $db = Application::$app->db;
        $db->pdo->exec($sql);
        
        echo "Dropped contacts table\n";
    }
}
```

### 16.6 Migration naming convention

Follow this pattern for migration names:

- `m0001_` - Sequential number (starts at 0001)
- `_create_` - Action (create, add, drop, modify)
- `_table_name` - What's being changed

Examples:
- `m0001_create_users_table.php`
- `m0002_create_courses_table.php`
- `m0003_add_bio_to_users.php`
- `m0004_add_status_to_courses.php`
- `m0005_create_enrollments_table.php`

### 16.7 Running migrations

From the command line:

```bash
# Run pending migrations
php migrations.php

# Output:
# m0001_initials: up
# m0002_add_password_column: up
# Migrations complete
```

From PHP code:

```php
<?php
// In a controller or script
$migration = Application::$app->db->applyMigrations();
echo $migration;  // "Migrations complete" or error message
```

### 16.8 Migration best practices

- ✅ Create one migration per logical change
- ✅ Write descriptive class names
- ✅ Always implement both up() and down()
- ✅ Make migrations idempotent (safe to run multiple times)
- ✅ Keep migrations in version control
- ❌ Don't modify old migrations - create new ones instead
- ❌ Don't leave out the down() method

### 16.9 Why migrations matter

Migrations help with:

- **Version control** - Track schema changes over time
- **Collaboration** - Share schema changes with team
- **Rollback** - Undo changes if needed
- **Reproducibility** - Get same schema on different environments
- **Testing** - Run fresh migrations on test database

## 17. Working with Users and Roles

The sample app uses a user model and a login system.

Typical roles:

- `admin`
- `instructor`
- `student`

These roles can be used to:

- limit access to pages
- decide what actions are allowed
- display different interface elements for each user type

In this project, `AuthMiddleware` helps enforce access restrictions using the current user's category.

## 18. Security Considerations

Mandakini is lightweight and practical, but developers should still follow security basics.

### 18.1 Input validation - Never trust user input

```php
// ❌ BAD - No validation
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '" . $email . "'";

// ✅ GOOD - Use model validation
$model = new LoginForm();
$model->loadData($_POST);
if ($model->validate()) {
    $email = $model->email;  // Now it's safe
}
```

### 18.2 Password security - Hash before storing

```php
// ❌ BAD - Storing plain password
$user->password = $_POST['password'];
$user->save();

// ✅ GOOD - Hash the password
$user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$user->save();

// ✅ GOOD - Verify password on login
if (password_verify($_POST['password'], $user->password)) {
    Application::$app->login($user);
}
```

### 18.3 Access control - Use middleware

```php
// ❌ BAD - No permission check
public function deleteUserAction() {
    $userId = $_GET['id'];
    User::findOne(['loging_id' => $userId])->delete();
}

// ✅ GOOD - Protect with middleware
public function deleteUserAction() {
    // Only admins can delete users
    $this->setMiddleware(new AuthMiddleware(['admin']));
    
    $userId = $_GET['id'];
    User::findOne(['loging_id' => $userId])->delete();
}
```

### 18.4 SQL injection prevention - Use prepared statements

The framework uses PDO prepared statements in database helper methods:

```php
// ❌ BAD - SQL injection vulnerable
$login_id = $_POST['login_id'];
$query = "SELECT * FROM users WHERE loging_id = '" . $login_id . "'";

// ✅ GOOD - Framework handles safely
$user = User::findOne(['loging_id' => $login_id]);
```

### 18.5 Output encoding - Escape HTML

```php
// ❌ BAD - XSS vulnerability
<h1><?php echo $user->firstName; ?></h1>

// ✅ GOOD - Escape output
<h1><?php echo htmlspecialchars($user->firstName, ENT_QUOTES, 'UTF-8'); ?></h1>

// ✅ GOOD - Use short form
<h1><?= $user->firstName ?></h1>

// Or create a helper
<?php function safe($str) { 
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
} ?>
<h1><?php safe($user->firstName); ?></h1>
```

### 18.6 Session security - Use middleware

```php
// ✅ Session handled by framework
// Middleware automatically checks if user is logged in
public function profileAction() {
    $this->setMiddleware(new AuthMiddleware([]));
    
    // Only logged-in users reach here
    $user = Application::$app->user;
}
```

### 18.7 Secure configuration - Hide sensitive data

```php
// ❌ BAD - Sensitive data exposed
$config = [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=app',
        'username' => 'root',
        'password' => 'password123',  // Exposed!
    ]
];

// ✅ GOOD - Use environment variables
$config = [
    'db' => [
        'dsn' => getenv('DB_DSN'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),  // Hidden in .env
    ]
];
```

### 18.8 Error handling - Don't expose details in production

```php
// ❌ BAD - Error exposed to users
if (!$course) {
    die("SQL Error: Course not found in database");
}

// ✅ GOOD - Use graceful error handling
if (!$course) {
    throw new NotFoundException();
}

// In config.php
'debug' => false,  // Hide errors in production
```

### 18.9 File upload security

```php
<?php
public function uploadProfilePictureAction() {
    if (isset($_FILES['picture'])) {
        $file = $_FILES['picture'];
        
        // ✅ Validate file type
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type');
        }
        
        // ✅ Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large');
        }
        
        // ✅ Generate unique name
        $filename = uniqid() . '.' . $ext;
        
        // ✅ Save outside web root or in protected directory
        $uploadDir = dirname(__DIR__) . '/storage/uploads/';
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
        
        // Save to database
        $user = Application::$app->user;
        $user->profile_picture = $filename;
        $user->update(['loging_id' => $user->loging_id]);
    }
}
```

### 18.10 HTTPS enforcement

In production, always use HTTPS:

```php
// Force HTTPS in public/index.php
if (empty($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST'] !== 'localhost') {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}
```

## 19. Best Practices for Training and Team Use

For a team onboarding to this framework, follow these essential patterns:

### 19.1 File organization

**✅ Good structure:**
```
routes defined in: public/routes.php
controllers in: controllers/
models in: models/
views in: views/
database code stays in models/
HTML stays in views/
```

**❌ Avoid:**
```
Mixing HTML in controllers
SQL queries scattered everywhere
Business logic in views
```

### 19.2 Controller conventions

**✅ Good controller**
```php
<?php
namespace app\controllers;

class CourseController extends Controller {
    
    public function listAction() {
        // Get data
        $courses = Course::findAll();
        
        // Render view
        return $this->render('courses', ['courses' => $courses]);
    }
    
    public function viewAction() {
        // Get ID from request
        $id = $_GET['id'] ?? null;
        
        // Fetch from database
        $course = Course::findOne(['id' => $id]);
        
        if (!$course) {
            throw new NotFoundException();
        }
        
        // Return view
        return $this->render('course', ['course' => $course]);
    }
}
```

**❌ Avoid - Too much logic in controller**
```php
public function listAction() {
    $sql = "SELECT * FROM courses";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<html><body>";
    foreach ($courses as $course) {
        echo "<div>" . $course['name'] . "</div>";
    }
    echo "</body></html>";
}
```

### 19.3 Model validation best practices

**✅ Good model validation**
```php
<?php
namespace app\models;

class UserForm extends Model {
    public string $email = '';
    public string $phone = '';
    
    public function rules(): array {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'phone' => [[self::RULE_REGEX, 'pattern' => '/^\d{10}$/']],
        ];
    }
    
    public function labels(): array {
        return [
            'email' => 'Email Address',
            'phone' => 'Phone Number (10 digits)',
        ];
    }
}
```

**❌ Avoid - Validation in controller**
```php
public function registerAction() {
    $email = $_POST['email'];
    
    // Don't do validation here!
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email";
    }
}
```

### 19.4 View best practices

**✅ Good view**
```php
<!-- views/courseView.php -->
<div class="course">
    <h1><?= htmlspecialchars($course->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($course->description, ENT_QUOTES, 'UTF-8') ?></p>
    
    <div class="instructor">
        Taught by: <strong><?= htmlspecialchars($course->instructor_name, ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
    
    <button onclick="enrollCourse(<?= $course->id ?>)">Enroll</button>
</div>
```

**❌ Avoid - Too much logic in views**
```php
<!-- Don't do SQL in views! -->
<?php
$sql = "SELECT * FROM enrollments WHERE course_id = " . $course->id;
$stmt = Application::$app->db->pdo->prepare($sql);
$stmt->execute();
$count = $stmt->rowCount();
?>
Enrolled: <?= $count ?>
```

### 19.5 Daily workflow

A good daily development workflow:

1. **Plan feature** - What does the user need?
2. **Add route** - What URL accesses this feature?
3. **Create model** - What data and validation is needed?
4. **Write controller** - Handle the request
5. **Write view** - Display the output
6. **Test manually** - Does it work?
7. **Test edge cases** - What can break?
8. **Add middleware** - Is it secure?

### 19.6 Code review checklist

When reviewing code:

- ✅ Is the route defined in public/routes.php?
- ✅ Does the controller have proper middleware protection?
- ✅ Is validation happening in models?
- ✅ Are database queries using findOne/findAll/where methods?
- ✅ Is HTML in views, not controllers?
- ✅ Are forms using the Form helper?
- ✅ Is output properly escaped (htmlspecialchars)?
- ✅ Are sensitive operations protected with middleware?
- ✅ Is error handling graceful?

### 19.7 Documentation in code

```php
<?php
namespace app\controllers;

/**
 * CoursesController handles course-related pages and actions
 * 
 * Responsibilities:
 * - List all public courses
 * - Show detailed course information
 * - Handle enrollment requests
 */
class CoursesController extends Controller {
    
    /**
     * List all available courses
     * Route: GET /courses
     * 
     * @return string HTML view of all courses
     */
    public function listAction() {
        $courses = Course::findAll();
        return $this->render('courses', ['courses' => $courses]);
    }
    
    /**
     * Show a specific course
     * Route: GET /course/{id}
     * 
     * @return string HTML course detail page
     * @throws NotFoundException If course doesn't exist
     */
    public function viewAction() {
        $courseId = $_GET['id'] ?? null;
        $course = Course::findOne(['id' => $courseId]);
        
        if (!$course) {
            throw new NotFoundException();
        }
        
        return $this->render('course', ['course' => $course]);
    }
}
```

## 20. Typical Feature Development Flow

This is the standard way to build a new feature in Mandakini.

### Example: Create a user profile page

**Step 1: Add the route**

```php
// public/routes.php
$app->router->get('/profile', [AuthController::class, 'profile']);
```

**Step 2: Create the model (if needed)**

In this case, we're using an existing User model, but let's see a complete profile update model:

```php
<?php
// models/ProfileUpdateForm.php
namespace app\models;

use app\core\Model;

class ProfileUpdateForm extends Model {
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $bio = '';

    public function rules(): array {
        return [
            'firstName' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2]],
            'lastName' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2]],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'bio' => [[self::RULE_MAX, 'max' => 500]],
        ];
    }
    
    public function labels(): array {
        return [
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email Address',
            'bio' => 'Bio/About You',
        ];
    }
}
```

**Step 3: Create the controller action**

```php
<?php
// controllers/AuthController.php - Add this method
namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\ProfileUpdateForm;

class AuthController extends Controller {
    
    public function profileAction() {
        // Protect this page - only logged-in users can access
        $this->setMiddleware(new \app\core\middlewares\AuthMiddleware([]));
        
        // Get the current logged-in user
        $user = Application::$app->user;
        $model = new ProfileUpdateForm();
        
        // Pre-fill the form with current user data
        $model->firstName = $user->firstName;
        $model->lastName = $user->lastName;
        $model->email = $user->email;
        $model->bio = $user->bio ?? '';
        
        // Handle form submission
        if (Application::$app->request->isPost()) {
            $model->loadData(Application::$app->request->getBody());
            
            if ($model->validate()) {
                // Update user in database
                $user->firstName = $model->firstName;
                $user->lastName = $model->lastName;
                $user->email = $model->email;
                $user->bio = $model->bio;
                $user->update(['loging_id' => $user->loging_id]);
                
                // Show success message
                Application::$app->session->setFlash('success', 'Profile updated successfully!');
                
                // Reload the user
                $user = User::findOne(['loging_id' => $user->loging_id]);
                Application::$app->user = $user;
            }
        }
        
        // Render the profile view
        return $this->render('profile', [
            'user' => $user,
            'model' => $model,
            'title' => $user->getDisplayName() . ' Profile',
        ]);
    }
}
```

**Step 4: Create the view**

```php
<!-- views/profileView.php -->
<div class="profile-container">
    <h1><?php echo $user->getDisplayName(); ?>'s Profile</h1>
    
    <!-- Display flash message if update was successful -->
    <?php if (Application::$app->session->getFlash('success')): ?>
        <div class="alert alert-success">
            <?php echo Application::$app->session->getFlash('success'); ?>
        </div>
    <?php endif; ?>
    
    <!-- Show profile form -->
    <?php $form = \app\core\form\Form::begin('', 'post') ?>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $form->field($model, 'firstName')->textField() ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $form->field($model, 'lastName')->textField() ?>
            </div>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'email')->emailField() ?>
        </div>
        
        <div class="form-group">
            <?php echo $form->field($model, 'bio')->textareaField() ?>
        </div>
        
        <!-- View-only information -->
        <div class="form-group">
            <label>Member Since</label>
            <p class="form-control-static"><?php echo date('F j, Y', strtotime($user->created_at)); ?></p>
        </div>
        
        <div class="form-group">
            <label>Account Role</label>
            <p class="form-control-static"><?php echo ucfirst($user->category); ?></p>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/" class="btn btn-secondary">Back to Home</a>
        
    <?php \app\core\form\Form::end() ?>
</div>
```

**Step 5: Test in browser**

- Navigate to `/profile`
- Verify middleware protection (not logged in should redirect to login)
- Try modifying and updating profile
- Verify validation works
- Verify database updates

### Another example: Create a contact form feature

**Complete walkthrough:**

Step 1 - Route:
```php
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [SiteController::class, 'contact']);
```

Step 2 - Model:
```php
<?php
namespace app\models;
use app\core\Model;

class ContactForm extends Model {
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';

    public function rules(): array {
        return [
            'name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'subject' => [self::RULE_REQUIRED],
            'message' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 10]],
        ];
    }
}
```

Step 3 - Controller:
```php
public function contactAction() {
    $model = new ContactForm();
    
    if (Application::$app->request->isPost()) {
        $model->loadData(Application::$app->request->getBody());
        
        if ($model->validate()) {
            // Save to database or send email
            $contact = new Contact();
            $contact->name = $model->name;
            $contact->email = $model->email;
            $contact->subject = $model->subject;
            $contact->message = $model->message;
            $contact->status = 'new';
            $contact->save();
            
            Application::$app->session->setFlash('success', 
                'Thank you! We received your message and will contact you soon.');
            Application::$app->response->redirect('/');
            return '';
        }
    }
    
    return $this->render('contact', ['model' => $model]);
}
```

Step 4 - View:
```php
<div class="contact-container">
    <h1>Contact Us</h1>
    <p>Have a question? We'd love to hear from you.</p>
    
    <?php $form = \app\core\form\Form::begin('', 'post') ?>
        <?php echo $form->field($model, 'name')->textField() ?>
        <?php echo $form->field($model, 'email')->emailField() ?>
        <?php echo $form->field($model, 'subject')->textField() ?>
        <?php echo $form->field($model, 'message')->textareaField() ?>
        <button type="submit">Send Message</button>
    <?php \app\core\form\Form::end() ?>
</div>
```

Step 5 - Test
- Submit the form
- Verify validation errors
- Verify success message
- Check database for saved contact

## 21. Suggested Training Path for New Developers

A trainer can teach the framework in stages:

### Stage 1: Basics (Days 1-2)

**Concepts:**
- folder structure
- entry point
- routes
- controllers
- views

**Learning activities:**
1. Navigate the project folders
2. Trace how a request flows from index.php to a view
3. Create a simple static route showing a welcome page
4. Modify view to accept a variable from controller
5. Add multiple static pages

**Example exercise:**
```php
// Add route
$app->router->get('/welcome', [SiteController::class, 'welcome']);

// Create controller method
public function welcomeAction() {
    return $this->render('welcome', ['message' => 'Hello, Developer!']);
}

// Create view
<h1>Welcome Page</h1>
<p><?= $message ?></p>
```

### Stage 2: Data handling (Days 3-4)

**Concepts:**
- models and validation
- form submission
- request data
- error messages

**Learning activities:**
1. Create a form model with validation rules
2. Build a simple form in HTML
3. Use Form helper to generate form fields
4. Handle form submission in controller
5. Display validation errors

**Example exercise:**
```php
// Create model
class FeedbackForm extends Model {
    public string $name = '';
    public string $message = '';
    
    public function rules(): array {
        return [
            'name' => [self::RULE_REQUIRED],
            'message' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 5]],
        ];
    }
}

// Create controller
public function feedbackAction() {
    $model = new FeedbackForm();
    
    if (Application::$app->request->isPost()) {
        $model->loadData(Application::$app->request->getBody());
        
        if ($model->validate()) {
            echo "Thank you for your feedback!";
        }
    }
    
    return $this->render('feedback', ['model' => $model]);
}

// Display form with errors
<?php $form = Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'name')->textField() ?>
    <?php echo $form->field($model, 'message')->textareaField() ?>
    <button type="submit">Send</button>
<?php Form::end() ?>
```

### Stage 3: Database work (Days 5-7)

**Concepts:**
- DB models
- saving records
- finding records
- updating and deleting
- migrations

**Learning activities:**
1. Create database tables using migrations
2. Create a DBModel class
3. Save new records
4. Query and display records
5. Update and delete operations
6. Test with real database

**Example exercise:**
```php
// Create migration
class m0001_create_articles_table {
    public function up() {
        $sql = "CREATE TABLE articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP
        )";
        Application::$app->db->pdo->exec($sql);
    }
}

// Create model
class Article extends DBModel {
    public string $title = '';
    public string $content = '';
    
    public static function tableName(): string {
        return 'articles';
    }
    
    public function attributes(): array {
        return ['title', 'content'];
    }
}

// Save article
$article = new Article();
$article->title = $_POST['title'];
$article->content = $_POST['content'];
$article->save();

// Fetch articles
$articles = Article::findAll();
foreach ($articles as $article) {
    echo $article->title;
}
```

### Stage 4: Security and access (Days 8-9)

**Concepts:**
- login and logout
- session handling
- middleware
- authorization rules
- password hashing

**Learning activities:**
1. Create User model
2. Build login form
3. Implement login controller
4. Add middleware protection
5. Test protected pages
6. Implement logout
7. Test role-based access

**Example exercise:**
```php
// Create user registration
public function registerAction() {
    $model = new RegisterForm();
    
    if (Application::$app->request->isPost()) {
        $model->loadData(Application::$app->request->getBody());
        
        if ($model->validate()) {
            $user = new User();
            $user->loging_id = $model->loging_id;
            $user->password = password_hash($model->password, PASSWORD_DEFAULT);
            $user->save();
            
            Application::$app->login($user);
            Application::$app->response->redirect('/profile');
        }
    }
    
    return $this->render('register', ['model' => $model]);
}

// Protect page with middleware
public function profileAction() {
    $this->setMiddleware(new AuthMiddleware([]));  // Any logged-in user
    return $this->render('profile', ['user' => Application::$app->user]);
}

// Admin-only page
public function adminAction() {
    $this->setMiddleware(new AuthMiddleware(['admin']));  // Only admins
    return $this->render('admin');
}
```

### Stage 5: Advanced feature building (Days 10+)

**Concepts:**
- complex forms with multiple fields
- protected pages
- admin panels
- dynamic pages
- multi-role logic

**Learning activities:**
1. Build complete CRUD application
2. Create admin dashboard
3. Implement search/filtering
4. Build multi-step forms
5. Handle file uploads
6. Create reports

**Example exercise:**
Build a complete course management system:
- List all courses (public)
- View course details (public)
- Create course (instructor only)
- Edit course (instructor + own course, or admin)
- Enroll in course (student only)
- View enrollments (instructor views own, admin views all)

## 22. Labs and Exercises

A trainer can assign beginner exercises like:

### Exercise 1: Hello page

**Difficulty: Easy | Time: 15 minutes**

Create a route `/hello` that displays "Hello World".

**Requirements:**
- ✅ Add route in public/routes.php
- ✅ Create controller method
- ✅ Create view file
- ✅ View displays "Hello World"

**Solution:**
```php
// public/routes.php
$app->router->get('/hello', [SiteController::class, 'hello']);

// controllers/SiteController.php
public function helloAction() {
    return $this->render('hello');
}

// views/helloView.php
<h1>Hello World</h1>
```

### Exercise 2: Dynamic greeting

**Difficulty: Easy | Time: 20 minutes**

Create a route `/hello/{name}` that displays a personalized greeting.

**Requirements:**
- ✅ Accept name parameter in URL
- ✅ Display "Hello, [name]!" in the view
- ✅ Escape the name for safety

**Hints:**
- Use `$_GET['name']` to access parameter
- URL format: `/hello/john` or `/hello/alice`

### Exercise 3: Contact form

**Difficulty: Medium | Time: 45 minutes**

Create a form model with name and email validation.

**Requirements:**
- ✅ Create ContactForm model with validation
- ✅ Create /contact route (GET shows form, POST processes)
- ✅ Display validation errors if validation fails
- ✅ Show success message if valid

**Hints:**
- Use RULE_REQUIRED and RULE_EMAIL
- Use Form helper to generate form fields

### Exercise 4: Guest book

**Difficulty: Medium | Time: 2 hours**

Create a guest book where visitors can leave messages.

**Requirements:**
- ✅ Create database migration with guestbook table
- ✅ Create GuestbookEntry model
- ✅ Show form to add entries
- ✅ Save entries to database
- ✅ Display all previous entries

**Fields:**
- name (required, min 2 chars)
- email (required, email format)
- message (required, min 10 chars)

### Exercise 5: User registration

**Difficulty: Medium-Hard | Time: 3 hours**

Create a complete registration system.

**Requirements:**
- ✅ Create User database table
- ✅ Create User model
- ✅ Create registration form with validation
- ✅ Hash password securely
- ✅ Check for duplicate usernames/emails
- ✅ Redirect to login after successful registration

**Fields:**
- username (required, 3-20 chars, unique)
- email (required, email format, unique)
- password (required, min 8 chars)
- confirm password (must match password)

### Exercise 6: Protected profile page

**Difficulty: Hard | Time: 3 hours**

Create a login system with protected pages.

**Requirements:**
- ✅ Create login form
- ✅ Handle login (verify password)
- ✅ Store user in session
- ✅ Create profile page (protected)
- ✅ Create logout
- ✅ Redirect unauthenticated users to login

**Features:**
- Welcome message showing username
- Link to logout
- Redirect loop prevention

### Exercise 7: Admin panel with category-based access

**Difficulty: Hard | Time: 4 hours**

Allow different user access levels.

**Requirements:**
- ✅ Add 'category' column to users (admin, instructor, student)
- ✅ Create admin page (admin only)
- ✅ Create instructor dashboard (instructor + admin)
- ✅ Use AuthMiddleware for protection
- ✅ Show different pages based on role

These exercises help learners understand the flow of the framework step by step.

## 23. Common Problems and Solutions

These issues are normal during training and are useful for teaching debugging habits.

### Problem 1: Route not found (404 error)

**Symptom:** Visiting a URL shows "404 Page not found"

**Common causes:**
- Route not defined in public/routes.php
- URL doesn't match any defined route
- Typo in route path

**Solution:**
```php
// Check public/routes.php
$app->router->get('/profile', [AuthController::class, 'profile']);  // ✅ Correct

// ❌ Wrong - would cause 404
// Missing the route definition entirely

// Debug:
// 1. Add echo to routes.php to verify it's loaded
echo "Routes loaded";  // Should see this message

// 2. Check exact URL - must match exactly
// Route: /profile
// URL in browser: http://localhost/profile  ✅
// URL in browser: /profiles  ❌ (extra 's')
```

### Problem 2: Action method not found

**Symptom:** Error: "Method ... does not exist"

**Common causes:**
- Method name doesn't end with `Action`
- Method name doesn't match route definition
- Typo in method name

**Solution:**
```php
// ❌ Wrong method name
public function profileActionMethod() {}  // No method called this

// ✅ Correct convention
public function profileAction() {}

// Route definition must match:
$app->router->get('/profile', [AuthController::class, 'profile']);
//                                                        ^^^^^^^ <- no 'Action' here
```

### Problem 3: Controller not found

**Symptom:** Error: "Class not found" or "Cannot find controller"

**Common causes:**
- Controller not properly namespaced
- Controller filename doesn't match class name
- Autoloader not working

**Solution:**
```php
// ❌ Wrong namespace
class ProfileController {}  // Missing namespace

// ✅ Correct
namespace app\controllers;

class ProfileController extends Controller {}

// Filename must be: ProfileController.php
// Path: controllers/ProfileController.php
```

### Problem 4: View file not found

**Symptom:** Error: "View not found: profile"

**Common causes:**
- View filename doesn't follow naming convention
- View file doesn't exist
- Case sensitivity issues on Linux

**Solution:**
```php
// Controller code:
return $this->render('profile', ['user' => $user]);

// Expected view filenames (try in order):
// 1. views/profileView.php   ✅ Correct convention
// 2. views/profile.php
// 3. views/Profile.php

// ❌ Wrong filenames:
// views/profile_view.php
// views/ProfileView.php
```

### Problem 5: Form submission doesn't work

**Symptom:** Form submits but nothing happens, no error shown

**Common causes:**
- Form posts to wrong URL
- Controller doesn't check for POST request
- Middleware blocks submission
- Form field names don't match model properties

**Solution:**
```php
// ❌ Wrong - no POST check
public function loginAction() {
    $model = new LoginForm();
    $model->loadData($_POST);  // Always runs, even on GET
    // ...
}

// ✅ Correct
public function loginAction() {
    $model = new LoginForm();
    
    if (Application::$app->request->isPost()) {
        $model->loadData(Application::$app->request->getBody());
        if ($model->validate()) {
            // Process form
        }
    }
    
    return $this->render('login', ['model' => $model]);
}

// Form must post to correct URL:
<?php $form = Form::begin('', 'post') ?>

// Form fields must match model properties:
// Model: public string $loging_id = '';
// Form: <?php echo $form->field($model, 'loging_id')->textField() ?>
```

### Problem 6: Validation doesn't show errors

**Symptom:** Form submits but errors don't display

**Common causes:**
- Model validation not called
- Loop through errors not in view
- Error messages not displayed

**Solution:**
```php
// Controller:
if (Application::$app->request->isPost()) {
    $model->loadData(Application::$app->request->getBody());
    
    // ✅ Must call validate()
    if ($model->validate()) {
        // Success - save data
    } else {
        // Validation failed - show form with errors
        // Model now has $model->errors populated
    }
}

// View - show errors:
<?php $form = Form::begin('', 'post') ?>
    
    <!-- Field with automatic error display -->
    <?php echo $form->field($model, 'email')->emailField() ?>
    
    <!-- Manual error display -->
    <?php if ($model->hasError('email')): ?>
        <div class="error">
            <?php echo $model->getFirstError('email'); ?>
        </div>
    <?php endif; ?>
    
<?php Form::end() ?>
```

### Problem 7: User not staying logged in

**Symptom:** Login works but user is not remembered on next page

**Common causes:**
- Session not started
- User not stored properly in session
- Session cookie settings

**Solution:**
```php
// ✅ Session must be running (index.php starts it)
session_start();

// ✅ Login must be called to store user in session
Application::$app->login($user);

// ✅ Check user on each page:
if (!Application::isGuest()) {
    $user = Application::$app->user;
    echo "Logged in as: " . $user->loging_id;
}

// ✅ Make sure middleware is set on protected pages:
public function profileAction() {
    $this->setMiddleware(new AuthMiddleware([]));
    // Now only logged-in users can access
}
```

### Problem 8: Middleware blocking access unexpectedly

**Symptom:** Getting 403 Forbidden or redirected to login

**Causes:**
- Middleware role requirement too strict
- User role different than expected
- Wrong middleware array

**Solution:**
```php
// ❌ Wrong - empty array means ANY logged-in user
$this->setMiddleware(new AuthMiddleware([]));

// If user is guest: redirected to login
// If user is logged in: allowed

// ❌ Wrong - too strict
$this->setMiddleware(new AuthMiddleware(['admin']));

// Only users with category='admin' allowed
// Other roles get 403 Forbidden

// ✅ Correct - multiple roles
$this->setMiddleware(new AuthMiddleware(['admin', 'instructor']));

// Only admins and instructors allowed
// Students get 403 Forbidden

// Debug:
echo "User category: " . Application::$app->user->category;
```

### Problem 9: Database operations not saving data

**Symptom:** save() returns false or data not in database

**Causes:**
- Validation failing silently
- Database connection error
- Required fields empty
- SQL error in migration

**Solution:**
```php
// ✅ Check validation
$user = new User();
$user->loging_id = $_POST['loging_id'];
$user->email = $_POST['email'];

if (!$user->save()) {
    // Get errors
    echo "Errors: ";
    foreach ($user->errors as $errors) {
        echo implode(', ', $errors) . "; ";
    }
}

// ✅ Validate manually first
if (!$user->validate()) {
    echo "Validation failed";
    echo $user->getFirstError('email');
}

// ✅ Check database connection
$db = Application::$app->db;
var_dump($db->pdo);  // Should be PDO object, not null
```

### Problem 10: Debugging techniques

**Useful debugging methods:**

```php
// 1. echo or var_dump to see values
$user = User::findOne(['id' => 1]);
var_dump($user);  // See all properties

// 2. Check request data
var_dump(Application::$app->request->getBody());

// 3. Check session values
var_dump($_SESSION);

// 4. Test conditions
if (Application::isGuest()) {
    echo "User is guest";
} else {
    echo "User is logged in: " . Application::$app->user->loging_id;
}

// 5. Test database connection
try {
    $users = User::findAll();
    echo "Database working, found " . count($users) . " users";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}

// 6. Log to file
file_put_contents('debug.log', 
    date('Y-m-d H:i:s') . " - Debug message\n", 
    FILE_APPEND
);
```

## 24. Summary

Mandakini is a simple PHP framework designed to help developers build web applications without unnecessary complexity.

Its key strengths are:

- simplicity
- readability
- fast setup
- MVC structure
- database integration
- validation
- authentication
- beginner-friendly patterns

This framework is especially effective for training new developers because the code is easy to follow and the concepts map directly to common web development practices.

## 25. Recommended Training Message to New Developers

“Start by learning the route-controller-view cycle. Once you understand that, everything else in the framework becomes easier. Build small pages, test them, then add validation and database features gradually.”

This training philosophy keeps the learning process practical and manageable.

---

This guide book is intended to train developers and help them understand Mandakini in a structured way. It can be used as:
- A training manual for onboarding new team members
- Internal documentation for reference
- A learning resource for self-study
- A troubleshooting guide for common issues
- A best practices reference

**You now have a comprehensive guide to build web applications with Mandakini. Start with Section 20 to build your first feature, and refer to this guide as you grow your skills. Happy coding!**
