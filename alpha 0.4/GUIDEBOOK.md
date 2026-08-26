# Mandakini Framework Guide Book

This guide book is designed to train new developers, help teams understand the framework quickly, and serve as a practical handbook for building applications with Mandakini.

It explains the framework architecture, feature set, coding patterns, and best practices used in this project.

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

### 6.3 Controller layout support

Controllers can call `setLayout()` to switch layouts.

Example:

```php
$this->setLayout('mandakini_layout');
```

### 6.4 Middleware support

A controller can register middleware.

Example:

```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile']));
```

Middleware runs before the action to block unauthorized access.

## 7. Models and Validation

Models are stored in `models/` and extend `app\core\Model`.

A model usually represents:

- a form
- a data object
- a database-backed entity

### 7.1 Basic model example

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
}
```

### 7.2 `loadData()`

This method loads request input into model properties.

```php
$model->loadData($request->getBody());
```

### 7.3 `validate()`

The framework validates each field based on the rules array.

```php
if ($model->validate()) {
    // data is valid
}
```

### 7.4 Available validation rules

The framework supports many built-in rules:

- `RULE_REQUIRED`
- `RULE_EMAIL`
- `RULE_MIN`
- `RULE_MAX`
- `RULE_MATCH`
- `RULE_UNIQUE`
- `RULE_NUMARIC`
- `RULE_INT`
- `RULE_FLOAT`
- `RULE_DATE`
- `RULE_INLIST`
- `RULE_REGEX`
- `RULE_ALPHA`
- `RULE_ALPHA_PLUS_SPACE`
- `RULE_ALPHA_PLUS_SPACE_PLUS_DOT`
- `RULE_ALPHANUMARIC`
- `RULE_ALPHANUMARIC_PLUS_SPACE`

### 7.5 Error handling in models

The framework stores validation messages in `$errors`.

Examples:

```php
$model->hasError('email');
$model->getFirstError('email');
```

This is used to show errors in forms.

### 7.6 Labels and user-friendly names

`labels()` lets you set readable field labels.

```php
public function labels(): array {
    return [
        'loging_id' => 'Login ID',
        'firstName' => 'First Name',
    ];
}
```

This improves form output and validation messages.

## 8. Database Models

Database models are located in `models/` and extend `DBModel`.

This is the database-aware layer that gives the app persistence and query features.

### 8.1 Database model contract

A database model must define:

- `tableName()`
- `attributes()`
- `primaryKey()`
- `rules()`

Example:

```php
<?php
namespace app\models;

use app\core\db\DBModel;

class User extends DBModel {
    public string $loging_id = '';
    public string $email = '';

    public static function tableName(): string {
        return 'users';
    }

    public function attributes(): array {
        return ['loging_id', 'email'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
        ];
    }
}
```

### 8.2 Save, update, delete

The framework provides helpers like:

```php
$user->save();
$user->update(['loging_id' => $user->loging_id]);
$user->delete();
```

### 8.3 Query helpers

The `DBModel` includes static helper methods:

```php
User::findOne(['loging_id' => 'admin']);
User::findAll();
User::where('category', '=', 'student');
```

### 8.4 Transactions

The framework supports transaction handling through `DBModel` methods:

```php
User::transaction(function () {
    // perform several DB operations here
});
```

This is useful when multiple database changes must succeed together.

### 8.5 Supported databases

The framework includes support for:

- MySQL
- PostgreSQL
- SQL Server
- Oracle

The actual engine-specific classes live under `core/db`.

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

### 9.1 Layouts

The project uses layouts stored in `views/layout/`.

Examples:

- `main.php`
- `auth.php`
- `mandakini_layout.php`

The layout includes a placeholder:

```php
{{content}}
```

This tells the framework where the page content should appear.

### 9.2 Basic view example

```php
<h1>Welcome</h1>
<p>This is the home page.</p>
```

### 9.3 Rendering variables

You can pass variables into the view:

```php
return $this->render('profile', [
    'user' => $user,
    'title' => 'Profile',
]);
```

Inside the view, these variables are available as regular PHP variables.

## 10. Form Builder

The form helper is in `core/form` and is designed to reduce repetitive HTML creation.

### 10.1 Basic usage

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'email')->emailField() ?>
    <?php echo $form->field($model, 'password')->passwordField() ?>
    <button type="submit">Login</button>
<?php \app\core\form\Form::end() ?>
```

### 10.2 Supported fields

Supported types include:

- text
- password
- email
- number
- hidden
- textarea
- select
- checkbox
- radio
- file
- date
- time
- URL
- tel
- search

### 10.3 Select field example

```php
<?php echo $form->field($model, 'category')->selectField([
    'student' => 'Student',
    'instructor' => 'Instructor',
    'admin' => 'Admin',
]); ?>
```

### 10.4 File field example

```php
<?php echo $form->field($model, 'avatar')->fileField(); ?>
```

This is useful for upload-based features.

## 11. Authentication and Session Management

Mandakini includes a session-based user system.

### 11.1 Login flow

The project is set up to do this:

- load the user from the session
- find the user record by primary key
- create a logged-in user object
- allow protected pages to be accessed

Example:

```php
Application::$app->login($user);
```

### 11.2 Logout flow

```php
Application::$app->logout();
```

### 11.3 Guest checks

```php
if (Application::isGuest()) {
    // redirect to login
}
```

### 11.4 Role checks

The app includes logic that checks categories like:

- admin
- instructor
- student

This is implemented in the auth middleware and user model workflow.

## 12. Middleware

Middleware is used to authorize and gate actions before they are executed.

### 12.1 Base middleware

`core/middlewares/BaseMiddleware.php` defines the middleware base class.

### 12.2 Auth middleware

`AuthMiddleware` checks whether a user is allowed to access a route or action.

Example:

```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile', 'register']));
```

This allows a trainer to protect actions based on user type.

### 12.3 Why middleware matters

Middleware helps with:

- login required pages
- role-based access
- protected admin actions
- feature restrictions

## 13. Request and Response Lifecycle

A standard request flow in Mandakini looks like this:

1. Browser sends request
2. `Request` reads the path and method
3. `Router` finds the matching route
4. Controller action is invoked
5. Model validates or queries data
6. View renders HTML
7. `Response` sends output to browser

This gives a clean and easy-to-follow lifecycle for developers.

## 14. Routing Deep Dive

### 14.1 Static routes

```php
$app->router->get('/about', [SiteController::class, 'about']);
```

### 14.2 POST routes

```php
$app->router->post('/contact', [SiteController::class, 'contact']);
```

### 14.3 Dynamic route parameters

```php
$app->router->get('/course/{id}', [SiteController::class, 'courses']);
```

This is helpful for pages like profile, item details, or course pages.

### 14.4 URL parameter access

The router automatically sets values into the request state. In practice, the parameter can be accessed via the request or global variables.

## 15. Configuration

The application configuration is in `public/config.php`.

Example:

```php
$config = [
    'userClass' => \app\models\User::class,
    'appName' => 'Mandakini',
    'debug' => true,
    'db' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=mandakini',
        'username' => 'root',
        'password' => '',
    ]
];
```

### 15.1 Important config keys

- `userClass` tells the framework which user model to use
- `appName` sets the application name
- `debug` turns error visibility on or off
- `db` contains the database connection settings

## 16. Database Migrations

The project contains migration scripts under `migrations/` and a runner in `migrations.php`.

This helps developers manage database schema changes.

Example:

```php
echo $app->db->applyMigrations();
```

Migrations are useful for:

- creating tables
- adding columns
- updating schema
- versioning database changes

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

Mandakini is lightweight and practical, but developers should still follow security basics:

- never trust user input
- validate form fields
- hash passwords before storing them
- use middleware for protected routes
- encode output where needed
- avoid injecting raw SQL into user-controlled values

The framework includes a validation layer and uses PDO prepared statements in database access helpers, which is a good starting point for secure development.

## 19. Best Practices for Training and Team Use

For a team onboarding to this framework, the most important habits are:

1. Always add routes in `public/routes.php`
2. Keep controllers thin and readable
3. Use models for state and validation
4. Keep HTML in views
5. Reuse layouts for common structure
6. Protect pages using middleware
7. Keep database logic in DB models
8. Test features one page at a time

## 20. Typical Feature Development Flow

This is the standard way to build a new feature in Mandakini.

### Example: create a profile page

1. Add route
   ```php
   $app->router->get('/profile', [AuthController::class, 'profile']);
   ```

2. Add controller method
   ```php
   public function profileAction() {
       $user = Application::$app->user;
       return $this->render('profile', ['user' => $user]);
   }
   ```

3. Add view file
   ```php
   <h1>Profile</h1>
   <p><?php echo $user->firstName; ?></p>
   ```

4. Validate and protect as needed
   ```php
   $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile']));
   ```

5. Test the route in a browser

This workflow is the foundation of development in the framework.

## 21. Suggested Training Path for New Developers

A trainer can teach the framework in stages:

### Stage 1: Basics

- folder structure
- entry point
- routes
- controllers
- views

### Stage 2: Data handling

- models
- validation
- form submission
- request data

### Stage 3: Database work

- DB models
- saving records
- finding records
- updating and deleting
- migrations

### Stage 4: Security and access

- login
- session
- middleware
- authorization rules

### Stage 5: Advanced feature building

- custom forms
- protected pages
- admin panels
- dynamic pages
- multi-role logic

## 22. Labs and Exercises

A trainer can assign beginner exercises like:

### Exercise 1: Hello page

Create a route `/hello` and a view that prints Hello World.

### Exercise 2: Contact form

Create a form model with name and email validation.

### Exercise 3: User registration

Create a registration form and save a user in the database.

### Exercise 4: Protected profile page

Create a page that only logged-in users can access.

### Exercise 5: Category-based access

Allow only admin users to access an admin route.

These exercises help learners understand the flow of the framework step by step.

## 23. Common Problems to Watch For

- route not found
- wrong controller method name
- missing `Action` suffix
- wrong view filename
- route and action mismatch
- database settings not configured
- form field names not matching model property names
- middleware blocking access unexpectedly

These issues are normal in training and are useful for teaching debugging habits.

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

This guide book is intended to train developers and help them understand Mandakini in a structured way. It can be used as a training manual, internal documentation, or onboarding reference for new team members.
