# Mandakini Framework User Manual

Version: Alpha 0.4

Mandakini is a lightweight PHP MVC-style framework built for quick application development. It includes a simple router, controller system, model validation, database access helpers, form builders, views, layouts, and a session-based authentication flow.

This manual explains how to install the framework, understand its architecture, and build your own features using the conventions used in this project.

## 1. Overview

Mandakini is designed to keep development simple and readable. The project is organized around the core MVC pattern:

- `controllers/` handles requests and application logic
- `models/` defines data structures and validation rules
- `views/` contains UI templates
- `core/` contains the framework engine and reusable base classes
- `public/` contains the bootstrap file and route definitions
- `migrations/` stores database migration scripts

The framework is already configured for a typical PHP web app and includes sample pages for login, registration, contact, about, profile, and course routes.

## 2. Requirements

Before using the framework, make sure you have:

- PHP 8.0 or newer
- Composer
- MySQL, PostgreSQL, SQL Server, or Oracle database access
- A web server (Apache/Nginx/XAMPP/WAMP/MAMP)

Install dependencies:

```bash
composer install
```

## 3. Installation and Setup

1. Open the project in your IDE or local server.
2. Configure the database in `public/config.php`.
3. Ensure your document root points to the project root or the `public/` folder depending on your server setup.
4. Run migrations if you want the default database tables created:

```bash
php migrations.php
```

5. Open the app in the browser.

### Example configuration

```php
<?php
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

This file is read by `public/index.php` when the app starts.

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
│   └── form/
├── controllers/
├── models/
├── views/
├── migrations/
├── vendor/
├── composer.json
├── index.php
├── migrations.php
├── README.md
├── USERMANUAL.md
```

## 5. Application Bootstrap

The app bootstraps in `public/index.php`.

```php
<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use app\core\Application;

include_once __DIR__ . '/config.php';

$app = new Application(dirname(__DIR__), $config);
Application::$app = $app;

include_once __DIR__ . '/routes.php';

echo $app->run();
```

Key points:

- It loads Composer autoloading.
- It starts the session.
- It creates the `Application` singleton.
- It loads route definitions.
- It executes the request.

## 6. Routing

Routes are stored in `public/routes.php`.

Example:

```php
$app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);
$app->router->get('/profile', [AuthController::class, 'profile']);
$app->router->get('/course/{id}', [SiteController::class, 'courses']);
```

### HTTP methods

- `get()` for GET routes
- `post()` for POST routes

### Dynamic route parameters

The router supports placeholder route parameters like:

```php
$app->router->get('/course/{id}', [SiteController::class, 'courses']);
```

A URL such as `/course/php` will populate `$_GET['id']` and `$_REQUEST['id']` automatically.

## 7. Controllers

Each controller should extend `app\core\Controller`.

Example from the project:

```php
<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\core\Application;

class AuthController extends Controller {
    public string $id = 'Auth';

    public function __construct() {
        $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile', 'register']));
    }

    public function loginAction(Request $request) {
        $model = new LoginForm();
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            if ($model->validate() && $model->login()) {
                Application::$app->response->redirect('/');
                return;
            }
        }

        $this->setLayout('mandakini_layout');
        return $this->render('login', [
            'model' => $model,
        ]);
    }
}
```

### Controller conventions

- Method names follow `Action` suffix, for example `loginAction`.
- The route points to the controller class and method name without the suffix.
- `render()` renders a view and injects params.
- `setLayout()` switches the layout file used for the page.

### Layouts

Layouts are stored in `views/layout/`.

Example:

- `views/layout/main.php`
- `views/layout/auth.php`
- `views/layout/mandakini_layout.php`

The default layout is defined in `Application` as:

```php
public string $layout = 'main';
```

## 8. Models and Validation

Models extend `app\core\Model` or the database-aware `app\core\db\DBModel`.

### Basic model example

```php
<?php
namespace app\models;

use app\core\Model;

class LoginForm extends Model {
    public string $loging_id = '';
    public string $password = '';

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED, self::RULE_ALPHANUMARIC],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
        ];
    }

    public function labels(): array {
        return [
            'loging_id' => 'Login ID',
            'password' => 'Password',
        ];
    }
}
```

### Model validation rules

The framework includes these validation rules:

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

### Loading data into a model

```php
$model = new LoginForm();
$model->loadData($request->getBody());
if ($model->validate()) {
    // continue
}
```

### Working with errors

```php
if ($model->hasError('password')) {
    echo $model->getFirstError('password');
}
```

## 9. Database Models

Database-backed models extend `app\core\db\DBModel`.

Example from `models/User.php`:

```php
class User extends UserModel {
    public string $loging_id = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';
    public string $category = '';

    public static function tableName(): string {
        return 'users';
    }

    public function attributes(): array {
        return ['loging_id', 'firstName', 'lastName', 'email', 'password', 'category'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }
}
```

### Common DB methods

The DB model provides useful CRUD helpers:

```php
$user = new User();
$user->loadData($data);
$user->save();

$user->update(['loging_id' => $user->loging_id]);

User::findOne(['loging_id' => 'admin']);
User::findAll();
```

### Transactions

```php
User::transaction(function () {
    // all DB work happens in a transaction
});
```

## 10. Views and Layouts

Views are stored under `views/` and are automatically loaded by the framework.

The render process works like this:

- a controller calls `render('login', ['model' => $model])`
- `View::renderView()` loads the view file from `views/loginView.php`
- the selected layout from `views/layout/...` is loaded
- `{{content}}` in the layout is replaced with the view content

### Layout file pattern

```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->title; ?></title>
</head>
<body>
    {{content}}
</body>
</html>
```

### Example view

```php
<?php
/* @var $model \app\models\LoginForm */
?>

<div class="position-relative" style="height: 100vh;">
    <div class="position-absolute top-50 start-50 translate-middle col-md-6">
        <h1>Login</h1>
        <?php $form = \app\core\form\Form::begin('', 'post') ?>
            <?php echo $form->field($model, 'loging_id') ?>
            <?php echo $form->field($model, 'password')->passwordField() ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        <?php \app\core\form\Form::end() ?>
    </div>
</div>
```

## 11. Form Builder

Mandakini includes a simple form helper in `core/form`.

### Building a form

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'firstName') ?>
    <?php echo $form->field($model, 'email')->emailField() ?>
    <?php echo $form->field($model, 'password')->passwordField() ?>
    <button type="submit">Save</button>
<?php \app\core\form\Form::end() ?>
```

### Supported field types

The `Field` class supports many common HTML types:

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
- datetime-local
- time
- url
- tel
- search

### Example select field

```php
<?php echo $form->field($model, 'category')->selectField([
    'student' => 'Student',
    'instructor' => 'Instructor',
    'admin' => 'Admin',
]); ?>
```

## 12. Sessions, Authentication, and Middleware

The framework stores the logged-in user identifier in the session.

### Login flow

The login logic is handled in `LoginForm::login()`:

```php
$user = User::findOne(['loging_id' => $this->loging_id]);
if (!password_verify($this->password, $user->password)) {
    $this->addError('password', 'Password is incorrect');
    return false;
}
return Application::$app->login($user);
```

`Application::login()` stores the primary key in the session and sets the current user.

### Authorization

Middleware is used to restrict access to actions.

Example:

```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile', 'register']));
```

The middleware checks whether the user is authenticated and whether they are allowed to access the current controller action.

### Logging out

```php
public function logout() {
    Application::$app->logout();
    Application::$app->response->redirect('/');
}
```

## 13. Request Handling

The framework exposes request data via `Request`.

```php
$request->getPath();
$request->method();
$request->isGet();
$request->isPost();
$request->getBody();
```

This is useful when reading form submissions and route data.

## 14. Response Handling

The framework includes a `Response` object for redirects and status codes. Typical example:

```php
Application::$app->response->redirect('/');
```

## 15. Database Migrations

Database setup is handled through migration files in `migrations/` and the entry script `migrations.php`.

```php
$app = new Application(__DIR__, $config);
Application::$app = $app;

echo $app->db->applyMigrations();
```

Usually each migration creates or alters tables. This allows you to keep the schema under version control.

## 16. Creating a New Feature

A typical feature flow is:

1. Add a route in `public/routes.php`
2. Create a controller in `controllers/`
3. Add or update a model in `models/`
4. Add a view in `views/`
5. If data storage is needed, define or update the DB table
6. Test the form and route behavior

### Example: add a profile page

```php
$app->router->get('/profile', [AuthController::class, 'profile']);
```

```php
public function profileAction() {
    $user = Application::$app->user;
    $this->setLayout('mandakini_layout');
    return $this->render('profile', [
        'user' => $user,
        'title' => 'Profile',
    ]);
}
```

Then create `views/profileView.php`.

## 17. Best Practices

- Keep controller methods small and specific.
- Put validation logic in models.
- Use `DBModel` for database operations.
- Store route definitions in `public/routes.php`.
- Keep layouts reusable and minimal.
- Use `Application::$app->session->setFlash()` for user feedback messages.
- Always validate user input before saving or updating records.
- Use `password_hash()` when storing passwords.

## 18. Common Troubleshooting

### Route not found

- Check that the route is registered in `public/routes.php`.
- Ensure the HTTP method matches the route (`get` vs `post`).
- Verify the URL uses the correct path.

### Database connection errors

- Check the DSN and credentials in `public/config.php`.
- Ensure the database server is running.
- Verify the database name exists.

### Form validation fails unexpectedly

- Confirm the model has the matching property names.
- Make sure the field names in the form match the model attributes.
- Check that `loadData()` is being called with the request body.

### Views not rendering

- Verify the view file exists under `views/` with the correct naming pattern.
- Make sure the controller calls `render('something', ...)`.
- Confirm the layout file exists and includes `{{content}}`.

## 19. Sample Accounts

The demo app includes sample users such as:

- `admin` / `admin123`
- `instructor` / `instructor123`
- `student` / `student123`

These are useful for testing authentication and role-based access control.

## 20. Summary

Mandakini is a simple but effective PHP framework for beginner-friendly application development. It gives you a fast path to building MVC-based apps with:

- routing
- controllers
- model validation
- database access
- login/session management
- forms and layouts
- role-aware middleware

Once you understand the project conventions shown here, you can extend the framework by creating new controllers, models, routes, and views with minimal friction.

## 21. Next Steps

If you want to build your own app with this framework, start with:

1. Create a new controller.
2. Add a route and action.
3. Define a model with validation rules.
4. Create a view and layout.
5. Connect the model to a database table.
6. Test the app in the browser.

If you would like, this project can also be expanded with a full CRUD generator, admin panel scaffolder, or documentation for advanced database usage.

## Beginner Step-by-Step Guide

If you are new to Mandakini, follow these steps in order. This guide explains the simplest way to build a small web app with the framework.

### Step 1: Install the project and dependencies

Open a terminal in the project folder and run:

```bash
composer install
```

This installs the framework dependencies required by the project.

### Step 2: Configure the database

Open `public/config.php` and update the database connection.

```php
<?php
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

Important:

- Replace the database name if needed.
- Make sure MySQL is running.
- If you change the database name, also update your actual database.

### Step 3: Understand the startup flow

The app starts in `public/index.php`.

This file:

1. loads Composer autoloading
2. starts the session
3. creates the `Application` object
4. loads all routes
5. runs the application

So when you are learning the framework, remember this file is the entry point.

### Step 4: Add your first route

Routes are registered in `public/routes.php`.

Example:

```php
$app->router->get('/hello', [SiteController::class, 'hello']);
```

This means:

- when someone visits `/hello`
- the framework will call `SiteController::helloAction()'

You can add a route for your page before creating the controller method.

### Step 5: Create a controller

Create a controller under `controllers/`.

Example:

```php
<?php
namespace app\controllers;

use app\core\Controller;

class SiteController extends Controller {
    public function helloAction() {
        return $this->render('hello', [
            'title' => 'Hello Page',
        ]);
    }
}
```

A few important rules:

- controller class name must end with `Controller`
- method name must end with `Action`
- the route calls the method without the `Action` suffix

### Step 6: Create a view

Create a file named `views/helloView.php`.

Example:

```php
<h1>Hello from Mandakini</h1>
<p>This is my first page.</p>
```

The framework automatically loads the view file for the action.

### Step 7: Test the page

Run your local PHP server or open the app through your web server and visit:

```text
/hello
```

If everything is correct, you should see the page.

### Step 8: Add form data handling

To work with user input, create a model.

Example:

```php
<?php
namespace app\models;

use app\core\Model;

class ContactForm extends Model {
    public string $name = '';
    public string $email = '';

    public function rules(): array {
        return [
            'name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
        ];
    }
}
```

Then use it in a controller:

```php
$model = new ContactForm();
if ($request->isPost()) {
    $model->loadData($request->getBody());
    if ($model->validate()) {
        // save data or continue logic
    }
}
```

This is how Mandakini validates form submissions.

### Step 9: Render a form using the built-in form helper

The framework includes a form builder in `core/form`.

Example:

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'name') ?>
    <?php echo $form->field($model, 'email')->emailField() ?>
    <button type="submit">Send</button>
<?php \app\core\form\Form::end() ?>
```

This helps you quickly build HTML forms without writing repetitive code.

### Step 10: Create a database model

If the page needs to save data, create a model that extends `DBModel`.

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

Then use methods such as:

```php
User::findOne(['loging_id' => 'admin']);
User::findAll();
$user->save();
```

### Step 11: Add login and authentication

The framework already includes a login system and user model.

- `models/User.php` defines the user table
- `models/LoginForm.php` handles login validation
- `core/middlewares/AuthMiddleware.php` restricts access to protected pages

Example route:

```php
$app->router->get('/profile', [AuthController::class, 'profile']);
```

This route is protected and only available to authenticated users.

### Step 12: Create a layout

Layouts are stored in `views/layout/`.

Example layout file:

```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->title; ?></title>
</head>
<body>
    {{content}}
</body>
</html>
```

Your views appear inside the layout automatically.

### Step 13: Basic debugging

When `debug` is enabled in `public/config.php`, the app shows more error details.

```php
'debug' => true,
```

This helps while learning and testing your application.

### Step 14: Run your first full page

A beginner project usually follows this pattern:

1. add route
2. create controller action
3. create view file
4. validate form input
5. save or read data from database
6. render a page with layout

This is the full cycle used in Mandakini.

## Simple Example: Building a Home Page

Below is the simplest full beginner example.

### Route

```php
$app->router->get('/home', [SiteController::class, 'home']);
```

### Controller

```php
<?php
namespace app\controllers;

use app\core\Controller;

class SiteController extends Controller {
    public function homeAction() {
        return $this->render('home', [
            'title' => 'Home',
        ]);
    }
}
```

### View

```php
<h1>Welcome to Mandakini</h1>
<p>This is your first page.</p>
```

### Result

Visit `/home` in the browser and the page appears.

## Recommended Beginner Workflow

Use this workflow every time you build a page:

1. Add a route in `public/routes.php`
2. Create a controller method
3. Create a view file under `views/`
4. Add validation model if the page has forms
5. Test the page in the browser
6. Add database operations if needed
7. Protect routes with middleware if access should be restricted

## Common Beginner Mistakes

- forgetting to add the route
- naming the controller method incorrectly
- forgetting the `Action` suffix
- using the wrong view filename
- not setting the database config correctly
- forgetting to include `loadData($request->getBody())` for form submission
- using the wrong form field names

## Beginner Tips

- Start with a page that does not use a database.
- Test one route at a time.
- Keep your controller methods simple.
- Use one model per form or data thing.
- Always check `public/routes.php` first when something does not work.
- Use `debug => true` while learning.

## Next Step After Learning the Basics

Once you are comfortable with the simple flow, you can move on to:

- creating multiple pages
- adding database tables
- using validation rules
- creating custom middleware
- building forms with select fields and file uploads
- implementing login and user management

This beginner flow is the foundation for everything else in the framework.

---

This section is meant to give beginners a practical and easy introduction. Once you are comfortable with these steps, continue with the later sections of this manual for deeper framework concepts.
