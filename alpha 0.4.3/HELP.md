# Mandakini Framework Help

This help file explains what the framework is, how it works, and how to use it for everyday web development.

## 1. What is Mandakini?

Mandakini is a lightweight PHP MVC-style framework built for simple, fast web application development.

It gives you:

- routing
- controllers
- model validation
- database support
- form helpers
- session handling
- authentication middleware
- view/layout rendering

It is designed for beginners and small projects, but it can also be extended for bigger applications.

## 2. Main Features

### Routing

You define routes in `public/routes.php`.

Example:

```php
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->post('/login', [AuthController::class, 'login']);
```

The router supports:

- GET routes
- POST routes
- parameterized routes such as `/course/{id}`

### MVC structure

The framework uses a simple MVC organization:

- `controllers/` for actions
- `models/` for data and validation
- `views/` for HTML pages
- `core/` for framework logic

### Model validation

Models can define rules for validation.

Example:

```php
public function rules(): array {
    return [
        'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
        'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
    ];
}
```

### Database support

The framework supports database models and includes helpers for:

- saving records
- updating records
- deleting records
- finding single records
- finding many records
- transactions

### Form builder

The framework contains built-in form helpers to render input fields quickly.

Example:

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'email')->emailField() ?>
    <?php echo $form->field($model, 'password')->passwordField() ?>
<?php \app\core\form\Form::end() ?>
```

### Authentication

The project includes session-based authentication with a middleware layer.

Common examples:

- login page
- profile page
- protected routes
- admin/instructor/student access checks

### Layouts

Views are rendered inside layouts stored in `views/layout/`.

The layout uses a placeholder like:

```php
{{content}}
```

This allows you to have a consistent site design across pages.

## 3. Project Structure

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
│   ├── View.php
│   └── form/
├── controllers/
├── models/
├── views/
├── migrations/
├── vendor/
├── README.md
├── USERMANUAL.md
├── HELP.md
```

## 4. Quick Start

### Step 1: Install dependencies

```bash
composer install
```

### Step 2: Configure database

Edit `public/config.php`.

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

### Step 3: Start the app

Use your PHP server or web server to serve the project. Usually the app loads from `public/index.php`.

### Step 4: Add a route

Open `public/routes.php` and add:

```php
$app->router->get('/hello', [SiteController::class, 'hello']);
```

### Step 5: Create a controller

Create `controllers/SiteController.php`:

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

### Step 6: Create a view

Create `views/helloView.php`:

```php
<h1>Hello from Mandakini</h1>
<p>This is my first page.</p>
```

Visit `/hello` in the browser.

## 5. How Controllers Work

Controllers should extend `app\core\Controller`.

Example:

```php
class AuthController extends Controller {
    public function loginAction(Request $request) {
        $model = new LoginForm();
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            if ($model->validate()) {
                // login logic here
            }
        }

        return $this->render('login', ['model' => $model]);
    }
}
```

Rules:

- class name ends with `Controller`
- method name ends with `Action`
- route calls method without `Action`

## 6. How Models Work

Create models in `models/`.

```php
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

Use `loadData()` to fill the model from the request and `validate()` to check it.

## 7. How Database Models Work

Database models extend `app\core\db\DBModel`.

Example:

```php
class User extends UserModel {
    public static function tableName(): string {
        return 'users';
    }

    public function attributes(): array {
        return ['loging_id', 'email', 'password'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }
}
```

Common methods:

```php
User::findOne(['loging_id' => 'admin']);
User::findAll();
$user->save();
$user->update(['loging_id' => $user->loging_id]);
```

## 8. How Views Work

Views are PHP files in `views/` and usually named like:

- `homeView.php`
- `loginView.php`
- `profileView.php`

The view is rendered through the controller:

```php
return $this->render('login', ['model' => $model]);
```

The framework loads the file:

```text
views/loginView.php
```

## 9. Using the Form Helper

The form helper is in `core/form`.

Example:

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'firstName') ?>
    <?php echo $form->field($model, 'email')->emailField() ?>
    <?php echo $form->field($model, 'password')->passwordField() ?>
<?php \app\core\form\Form::end() ?>
```

Common field types include:

- text
- password
- email
- number
- textarea
- select
- checkbox
- file
- date

## 10. Authentication and Middleware

Routes can be protected using middleware.

Example:

```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile']));
```

This checks whether the user is logged in or authorized to access a route.

## 11. Session and User Access

The application has a session object and a logged-in user.

Example:

```php
Application::$app->login($user);
Application::$app->logout();
```

You can also check if the current user is a guest:

```php
if (Application::isGuest()) {
    // redirect to login
}
```

## 12. Common Problems and Fixes

### Route not found

Check:

- the route exists in `public/routes.php`
- the path matches exactly
- the method is correct (`get` or `post`)

### Database connection errors

Check:

- DSN in `public/config.php`
- username and password
- database server running
- database exists

### Form validation fails

Check:

- `loadData($request->getBody())` was called
- model property names match form input names
- rules are defined correctly

### View not showing

Check:

- the view file exists
- controller method is named correctly
- render() is called with the right view name

## 13. Best Practices

- Keep controllers focused and simple.
- Keep validation in models.
- Put database logic in DB models.
- Keep layout files reusable.
- Use `debug => true` during development.
- Use middleware for protected pages.

## 14. Typical Beginner Workflow

Use this workflow for most new features:

1. Add a route in `public/routes.php`
2. Create a controller action
3. Create a view file
4. Add or update a model
5. Validate form data
6. Save/query database if required
7. Test in the browser

## 15. Sample Login Accounts

The sample app includes demo users such as:

- `admin` / `admin123`
- `instructor` / `instructor123`
- `student` / `student123`

## 16. Good Next Steps

If you are learning the framework, try these next:

- build a contact page
- build a product list page
- create a login page
- add a register form
- create a protected admin page
- connect a table to a database

## 17. Related Files

For more detail, see:

- `README.md`
- `USERMANUAL.md`
- `public/routes.php`
- `public/config.php`
- `controllers/`
- `models/`
- `views/`

## 18. Final Note

Mandakini is easy to learn because it follows a simple rule:

- route -> controller -> model -> view

If you build small features one step at a time, the framework is very approachable for beginners.

If you want help creating a new page, login system, or database feature, you can follow the structure in this project and extend it gradually.

## 19. Quick Reference

### Common route example

```php
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->post('/login', [AuthController::class, 'login']);
```

### Common controller example

```php
class SiteController extends Controller {
    public function homeAction() {
        return $this->render('home', ['title' => 'Home']);
    }
}
```

### Common model example

```php
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

### Common form example

```php
<?php $form = \app\core\form\Form::begin('', 'post') ?>
    <?php echo $form->field($model, 'loging_id') ?>
    <?php echo $form->field($model, 'password')->passwordField() ?>
    <button type="submit">Login</button>
<?php \app\core\form\Form::end() ?>
```

### Common database example

```php
$user = User::findOne(['loging_id' => 'admin']);
$user->save();
User::findAll();
```

### Common redirect example

```php
Application::$app->response->redirect('/');
```

### Common auth check

```php
if (Application::isGuest()) {
    Application::$app->response->redirect('/login');
}
```

## 20. FAQ

### Q: Where do I define routes?
A: In `public/routes.php`.

### Q: Where do I put controllers?
A: In `controllers/`.

### Q: What file is the app entry point?
A: `public/index.php`.

### Q: What file holds database settings?
A: `public/config.php`.

### Q: What should my controller methods end with?
A: `Action`.

### Q: What should my controller class names end with?
A: `Controller`.

### Q: How do I create a page?
A: Add a route, create a controller action, and create a matching view file in `views/`.

### Q: How do I validate form input?
A: Use a model with `rules()` and call `loadData($request->getBody())` and `validate()`.

### Q: How do I redirect a user?
A: Use `Application::$app->response->redirect('/some-page');`.

### Q: How do I protect a page?
A: Add middleware with `setMiddleware(...)` or use the existing auth middleware.

### Q: Why is my page not loading?
A: Check the route definition, controller name, file name, and view name.

### Q: Why is my database not connecting?
A: Check the DSN, username, password, database exists, and server is running.

### Q: Can I create custom validation rules?
A: Yes, you can add them inside the model validation logic or extend the framework behavior with custom methods.

### Q: Can I use this for a beginner project?
A: Yes. This framework is designed to be simple and easy to learn for small MVC-style applications.

### Q: Where should I start learning?
A: Start by creating a simple route, then a controller, then a view, then a form with validation.

## 21. Beginner Friendly Summary

If you are just starting, remember this simple flow:

1. add a route
2. create a controller
3. create a view
4. validate input using a model
5. save or read data if needed
6. protect pages with middleware

This simple pattern is the foundation of Mandakini development.

If you want help creating a real feature, start with one page at a time. The framework is easiest to learn when you build small pieces and test them immediately.

## 22. Common Errors and Fixes

### Error: "Route not found"
Cause: the route is missing or the method is wrong.

Fix:

```php
$app->router->get('/hello', [SiteController::class, 'hello']);
```

Check that the URL and HTTP method match the route definition.

### Error: "Class not found"
Cause: the controller file is missing or the namespace is wrong.

Fix:

- create the file in `controllers/`
- use the correct namespace
- ensure the class ends with `Controller`

### Error: "View not found"
Cause: the view file name does not match the controller action or the file is missing.

Fix:

```php
return $this->render('login', ['model' => $model]);
```

This expects `views/loginView.php`.

### Error: "Database connection failed"
Cause: wrong DSN, username, password, or database not running.

Fix:

- check `public/config.php`
- verify MySQL is running
- verify the database exists

### Error: "Form validation failed unexpectedly"
Cause: input names do not match model properties or `loadData()` was not called.

Fix:

```php
$model->loadData($request->getBody());
if ($model->validate()) {
    // good
}
```

### Error: "The page redirects to the wrong place"
Cause: wrong redirect path.

Fix:

```php
Application::$app->response->redirect('/');
```

### Error: "User is not logged in"
Cause: route is protected and there is no session user.

Fix:

- log in first
- verify the session is active
- check middleware configuration

## 23. Quick Cheat Sheet

### Start the app

```bash
composer install
```

### Add a route

```php
$app->router->get('/hello', [SiteController::class, 'hello']);
```

### Create a controller

```php
class SiteController extends Controller {
    public function helloAction() {
        return $this->render('hello', ['title' => 'Hello']);
    }
}
```

### Create a view

```php
<h1>Hello</h1>
```

### Validate a form

```php
$model->loadData($request->getBody());
if ($model->validate()) {
    // proceed
}
```

### Protect a page

```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile']));
```

### Redirect

```php
Application::$app->response->redirect('/login');
```

### Save data

```php
$user->save();
```

### Find a record

```php
$user = User::findOne(['loging_id' => 'admin']);
```

### Basic rule example

```php
'email' => [self::RULE_REQUIRED, self::RULE_EMAIL]
```

### Basic login check

```php
if (Application::isGuest()) {
    Application::$app->response->redirect('/login');
}
```

## 24. Useful Links

- `README.md` — project overview and quick facts
- `USERMANUAL.md` — full guide with details and examples
- `HELP.md` — quick help and troubleshooting
- `public/routes.php` — all routes
- `public/config.php` — database and app settings

---

If you are using the framework for the first time, keep this file open while you build your first page. Most beginner problems are solved by checking the route, controller, view, and database configuration in that order.
