# Mandakini Framework User Manual

## 1. What is Mandakini Framework?
Mandakini is a lightweight PHP web framework built for beginners. It helps you create pages, handle forms, validate input, authenticate users, and work with databases using MVC-style structure.

This manual explains how to set up the framework, how the main files are organized, and how to build a small app with controllers, models, views, and routes.

## 2. Prerequisites
- PHP 8.x or newer
- Composer
- A supported database server: MySQL, PostgreSQL, MS SQL Server, or Oracle
- A web server configured to serve `public/` in the document root (for example Apache, Nginx, or PHP built-in server)

## 3. Installation
1. Open a terminal in the project root: `<path>\mandakini-framework`
2. Install Composer dependencies:
   - `composer install`
3. Copy or update `public/config.php` with your database settings.
4. Make sure `public/index.php` is the application entry point.

## 4. Folder structure overview
- `public/` – Web root. Contains `index.php`, `config.php`, and `routes.php`
- `core/` – Framework core classes: `Application`, `Router`, `Controller`, `Model`, `View`, `Request`, `Response`, `Session`
- `core/db/` – Database classes and models
- `core/form/` – Form helper classes for rendering form fields
- `controllers/` – Application controllers
- `models/` – Application model classes
- `views/` – View templates and layout files
- `migrations/` – Database migration files
- `vendor/` – Composer packages and autoload files

## 5. Configuration
The main configuration file is `public/config.php`.

Example:
```php
$config = [
    'userClass' => \app\models\User::class,
    'appName' => 'Mandakini',
    'debug' => false,
    'db' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=test',
        'username' => 'root',
        'password' => '',
    ]
];
```

Important settings:
- `userClass` — user model used for authentication
- `appName` — application name shown in views
- `debug` — enable or disable error reporting
- `db.dsn` — PDO DSN string
- `db.username` and `db.password`

## 6. How the app boots
The entry point is `public/index.php`.

It does:
- loads Composer autoload
- starts PHP session
- loads `config.php`
- creates an `Application` instance
- loads routes from `public/routes.php`
- runs the application with `$app->run()`

## 7. Routing
Routes are defined in `public/routes.php`.

Examples:
```php
$app->router->get('/', [SiteController::class, 'home']);
$app->router->post('/login', [AuthController::class, 'login']);
```

Supported methods:
- `get($path, $callback)`
- `post($path, $callback)`

A callback can be:
- controller action: `[ControllerClass::class, 'method']`
- view name string (not used in this app example)

The router resolves the current request path and method, then calls the matching controller action.

## 8. Controllers
Controllers are classes under `controllers/` that extend `app\core\Controller`.

A controller action usually returns a view using `$this->render()`.

Example from `controllers/AuthController.php`:
```php
public function loginAction(Request $request) {
    $model = new LoginForm();
    if ($request->isPost()) {
        $model->loadData($request->getBody());
        if ($model->validate() && $model->login()) {
            Application::$app->response->redirect('/');
            return;
        }
    }
    $this->setLayout('itdlh_landing_new');
    return $this->render('login', ['model' => $model]);
}
```

Controller features:
- `$this->render($view, $params)` renders a view inside the layout
- `$this->renderViewOnly($view, $params)` renders a view without layout
- `$this->setLayout('layout_name')` changes the layout file in `views/layout/`
- `$this->setMiddleware($middleware)` or `$this->registerMiddleware($middleware)` adds route protection

## 9. Views and layouts
Views are PHP templates stored in `views/`.
Layouts are in `views/layout/`.

Standard rendering flow:
1. `render($view, $params)` loads `views/$view.php`
2. it injects view content into `views/layout/$layout.php`
3. the layout should contain `{{content}}`

Example layout file path:
- `views/layout/main.php`

Example view path:
- `views/login.php`

The view receives variables from the controller via the `$params` array.

## 10. Models and database access
Mandakini uses an active-record style model system.

Base classes:
- `app\core\Model` — validation and data loading
- `app\core\db\DBModel` — database save, update, delete, findOne, findAll

Create a database-backed model by extending `app\core\db\DBModel`.

### 10.1 Database-specific model classes
Mandakini provides special DBModel subclasses for different database engines. These classes automatically discover table columns and primary keys from the database schema, which makes model setup easier.

- `app\core\db\MySqlDBModel`
  - Looks up `INFORMATION_SCHEMA.COLUMNS` in MySQL
  - Excludes auto-increment primary key columns from `attributes()` so inserts do not set them
  - Uses `COLUMN_TYPE` metadata for `getColumnTypes()`
  - Default primary key falls back to `id` when not found

- `app\core\db\MSSQLServerDBModel`
  - Reads SQL Server catalog views under `INFORMATION_SCHEMA`
  - Detects identity columns using `COLUMNPROPERTY(..., 'IsIdentity')`
  - Finds primary key columns from `INFORMATION_SCHEMA.TABLE_CONSTRAINTS`
  - Builds SQL Server-style type strings like `varchar(255)` and `decimal(10,2)`

- `app\core\db\PostgresDBModel`
  - Reads PostgreSQL `information_schema.columns`
  - Detects serial/identity primary keys by checking `column_default` for `nextval(...)` or `is_identity = 'YES'`
  - Builds type strings using `data_type`, `character_maximum_length`, and numeric precision/scale

- `app\core\db\OracleDBModel`
  - Reads Oracle metadata from `ALL_TAB_COLUMNS` and `ALL_CONSTRAINTS`
  - Uses the current schema from `SYS_CONTEXT('USERENV', 'CURRENT_SCHEMA')`
  - Detects identity primary keys using `IDENTITY_COLUMN = 'YES'`
  - Uses uppercased table names when querying Oracle schema metadata

Use these subclasses when your model targets a specific database engine. For example:
```php
class Course extends \app\core\db\MySqlDBModel {
    public string $name = '';
    public static function tableName(): string { return 'courses'; }
}
```

When using the generic `DBModel`, you must define both `attributes()` and `primaryKey()` manually. The engine-specific classes remove that boilerplate for supported databases.

Example `User` model:
```php
class User extends UserModel {
    public string $loging_id = '';
    public string $firstName = '';
    public string $email = '';

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

### 10.2 CRUD operations in `DBModel`
`app\core\db\DBModel` provides the core CRUD methods used by all database-backed models.

- `save()`
  - Inserts a new record into the database.
  - Uses the model's `attributes()` list to build an `INSERT` statement.
  - Binds each attribute value safely with PDO parameter binding.

- `update($where)`
  - Updates existing records matching the `$where` condition.
  - Builds an `UPDATE` statement using `attributes()` for the values and the `$where` array for the `WHERE` clause.
  - Example: `$model->update(['id' => 5]);`

- `delete($where)`
  - Deletes records from the table.
  - If `$where` is empty, it deletes the row using the model's primary key value.
  - Example: `$model->delete(['id' => 5]);`

- `findOne($where)`
  - Loads a single record from the database using the given conditions.
  - Returns an instance of the model class or `false` if none was found.
  - Example: `User::findOne(['loging_id' => 'admin']);`

- `findAll($where, $orderBy, $limit)`
  - Loads multiple rows from the database.
  - Supports optional `WHERE`, `ORDER BY`, and `LIMIT` clauses.
  - Example: `Course::findAll(['category' => 'web'], 'course_id DESC', ['offset' => 0, 'row_count' => 20]);`

### 10.3 CRUD behavior in engine-specific models
The engine-specific subclasses inherit the CRUD behavior from `DBModel`. Their special job is schema discovery rather than changing the basic CRUD methods.

- `app\core\db\MySqlDBModel`
  - Automatically determines `attributes()` and `primaryKey()` from MySQL `INFORMATION_SCHEMA.COLUMNS`.
  - Excludes auto-increment primary keys from `attributes()` so `save()` does not attempt to insert them.
  - Provides `getColumnTypes()` metadata for the table.

- `app\core\db\MSSQLServerDBModel`
  - Uses SQL Server `INFORMATION_SCHEMA` and `COLUMNPROPERTY(..., 'IsIdentity')` to detect identity columns.
  - Automatically builds the model's `attributes()` list and primary key.
  - Uses the same `save()`, `update()`, `delete()`, `findOne()`, `findAll()` CRUD methods from `DBModel`.

- `app\core\db\PostgresDBModel`
  - Uses PostgreSQL `information_schema.columns` and primary key metadata.
  - Detects serial/identity columns from `column_default` and `is_identity`.
  - Allows `save()` and `update()` to work without manually listing all columns.

- `app\core\db\OracleDBModel`
  - Uses Oracle metadata from `ALL_TAB_COLUMNS` and primary key constraints.
  - Detects identity primary keys with `IDENTITY_COLUMN = 'YES'`.
  - Also inherits the standard `DBModel` CRUD methods.

### 10.4 Setting up an engine-specific DB model
To use a database-specific model class, follow these steps:

1. Set the correct PDO DSN in `public/config.php`.
   - MySQL: `mysql:host=localhost;port=3306;dbname=your_db`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=your_db`
   - SQL Server: `sqlsrv:Server=localhost;Database=your_db`
   - Oracle: `oci:dbname=//localhost:1521/your_service_name`

2. Use the corresponding model base class in your model file.
   - `use app\core\db\MySqlDBModel;`
   - `use app\core\db\PostgresDBModel;`
   - `use app\core\db\MSSQLServerDBModel;`
   - `use app\core\db\OracleDBModel;`

3. Extend the engine-specific class and define only `tableName()`.
   ```php
   class Course extends \app\core\db\PostgresDBModel {
       public static function tableName(): string {
           return 'courses';
       }
   }
   ```

4. Ensure your database table exists and your PDO user has permission to read schema metadata.
   - For MySQL, the table must be in the same database defined by `DATABASE()`.
   - For PostgreSQL, the table must be in the current schema.
   - For SQL Server, the table must be in the current database and schema.
   - For Oracle, the table name is queried in uppercase by default.

5. If you prefer manual model definitions, extend `DBModel` and supply `attributes()` and `primaryKey()` yourself.

In summary, CRUD operations work the same for all models. The engine-specific subclasses just make schema setup easier for their database type.

Methods available:
- `save()` — insert a new record
- `update($where)` — update an existing row
- `delete($where)` — delete a row
- `findOne($where)` — return one record
- `findAll($where, $orderBy, $limit)` — return records array

### 10.5 Transaction processing

`DBModel` includes built-in transaction helpers that wrap PDO transactions on the framework's DB connection. These helpers let you control transactions manually or run a callable inside a managed transaction that automatically commits or rolls back on error.

Provided methods (in `app\core\db\DBModel`):

- `public static function beginTransaction(): bool` — starts a PDO transaction (`PDO::beginTransaction`).
- `public static function commitTransaction(): bool` — commits the current transaction (`PDO::commit`).
- `public static function rollBackTransaction(): bool` — rolls back the current transaction (`PDO::rollBack`).
- `public static function transaction(callable $callback)` — convenience wrapper that begins a transaction, executes the callback, commits if the callback completes normally, or rolls back and rethrows if an exception/error occurs.

Behavior and best practices:

- The transaction helpers operate on `Application::$app->db->pdo`. Ensure your DB connection is configured before using transactions.
- Use the `transaction()` wrapper for concise and safe transactional work — it guarantees rollback on exceptions and rethrows the exception so callers can handle errors:

    ```php
    DBModel::transaction(function() use ($order, $user) {
            $user->save();
            $order->save();
            // return any value you need; transaction() will return it
            return true;
    });
    ```

- For manual control (when you need multiple commits/rollbacks or custom error handling), use explicit begin/commit/rollback:

    ```php
    DBModel::beginTransaction();
    try {
            $user->save();
            $order->save();
            DBModel::commitTransaction();
    } catch (\Throwable $e) {
            DBModel::rollBackTransaction();
            throw $e; // or handle the error
    }
    ```

- Note about nested transactions: PDO itself does not provide true nested transactions across all drivers. The `transaction()` wrapper and the simple begin/commit/rollback helpers do not manage savepoints. If you need nested transactions, implement database-specific savepoints using raw SQL on the PDO connection.

- The `transaction()` wrapper checks the PDO transaction state and will only commit/rollback when appropriate; it is intended to reduce boilerplate and lower the risk of forgetting to rollback on errors.

### Loading request data into a model
Use `$model->loadData($request->getBody())`.
This fills model properties from form input names.

## 11. Validation
Define validation rules in your model using `rules()`.

Supported rules include:
- `required`
- `email`
- `min`
- `max`
- `match`
- `unique`
- `numaric`
- `integer`
- `float`
- `date`
- `regex`
- `alpha`
- `alpha + space`
- `alphanumaric`
- `in list`

### 11.1 Creating new validation rules
If you need a custom rule that is not built in, add it to `app\core\Model`.

Steps:
1. Add a new constant in `core/Model.php`, for example:
   ```php
   public const RULE_PHONE = 'phone';
   ```
2. Extend the `validate()` method in `core/Model.php` with a new check:
   ```php
   if ($ruleName === self::RULE_PHONE && !preg_match('/^[0-9]{10}$/', $value)) {
       $this->addErrorForRule($attribute, [self::RULE_PHONE, 'field' => $this->getLabel($attribute)]);
   }
   ```
3. Add an error message to `errorMessages()`:
   ```php
   self::RULE_PHONE => 'The {field} must be a valid 10-digit phone number',
   ```
4. Use the rule in your model `rules()`:
   ```php
   public function rules(): array {
       return [
           'phone' => [self::RULE_REQUIRED, self::RULE_PHONE],
       ];
   }
   ```

If you need a one-off validation rule that is specific to one model, you can also override `validate()` in that model and call `parent::validate()` first, then run extra checks.

Example model override:
```php
public function validate(): bool {
    $isValid = parent::validate();
    if ($this->age < 18) {
        $this->addError('age', 'Age must be at least 18');
        $isValid = false;
    }
    return $isValid;
}
```

### 11.2 Example: `CourseOnWeb` file upload validation
The `models/CourseOnWeb.php` model demonstrates a real custom validation method for uploaded image files.

Key behavior:
- It defines a custom rule constant: `RULE_ALLOWED_FILETYPE`
- It overrides `validate()` to inspect uploaded files for `course_image_file_land` and `course_image_file_detail`
- It checks the file extension against an allowed list: `jpg`, `png`, `jpeg`, `gif`, `webp`
- If an uploaded file has an invalid extension, it calls `addErrorForRule()` for the corresponding field
- After file checks, it returns `parent::validate()` so all normal model rules still run

The model also overrides `errorMessages()` to add a custom message for the new rule:
```php
public function errorMessages() {
    $messages = parent::errorMessages();
    $messages[self::RULE_ALLOWED_FILETYPE] = "Upload file should have only extentions ('jpg', 'jpeg', 'png', 'webp') ";
    return $messages;
}
```

In `CourseOnWeb::rules()`, the file field is declared like this:
```php
'course_image_land' => [self::RULE_REQUIRED, self::RULE_ALLOWED_FILETYPE],
```
This means the model will validate both presence and allowed type.

Example:
```php
public function rules(): array {
    return [
        'loging_id' => [self::RULE_REQUIRED],
        'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => self::class]],
        'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
        'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
    ];
}
```

Check if model is valid:
```php
if ($model->validate()) {
    $model->save();
}
```

Get the first error message:
- `$model->getFirstError('attributeName')`

## 12. Forms
The framework provides form helpers in `core/form/Form.php` and `core/form/Field.php`.

Example usage in a view:
```php
use app\core\form\Form;

$form = Form::begin('/register', 'post');
    echo $form->field($model, 'loging_id')->textField();
    echo $form->field($model, 'email')->emailField();
    echo $form->field($model, 'password')->passwordField();
    echo $form->field($model, 'confirmPassword')->passwordField();
    echo $form->field($model, 'category')->textField();
    echo $form->field($model, 'submit')->submitField();
Form::end();
```

Field types include:
- text, password, email, number, hidden, textarea
- select, checkbox, radio, file
- date, datetime-local, month, range, time, week, url, tel, search

## 13. Authentication and session
The framework manages login state with `app\core\Application` and `app\core\Session`.

Login logic example in `LoginForm`:
```php
$user = User::findOne(['loging_id' => $this->loging_id]);
if (password_verify($this->password, $user->password)) {
    return Application::$app->login($user);
}
```

Useful methods:
- `Application::$app->login($user)` — store logged-in user in session
- `Application::$app->logout()` — clear session user
- `Application::isGuest()` — check if no user is logged in
- `Application::$app->session->setFlash('success', 'Message')`
- `Application::$app->session->getFlash('success')`

## 14. Middleware and access control
Middleware lives in `core/middlewares/`.

The example `AuthMiddleware` protects actions based on user login status and user category.
Use it in controller constructor:
```php
$this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile', 'register']));
```

When a protected action executes, the middleware runs before the controller action.

## 15. Working with database migrations
Migrations are in the `migrations/` folder.

Run them with:
```bash
php migrations.php
```

This will create the migration table and apply new migration files.

## 16. Creating a new page in Mandakini
1. Add a route in `public/routes.php`:
   ```php
   $app->router->get('/hello', [SiteController::class, 'hello']);
   ```
2. Add a controller action in `controllers/SiteController.php`:
   ```php
   public function helloAction() {
       return $this->render('hello', ['title' => 'Hello', 'message' => 'Welcome']);
   }
   ```
3. Create the view file `views/hello.php`:
   ```php
   <h1><?= $title ?></h1>
   <p><?= $message ?></p>
   ```
4. Open `/hello` in your browser.

## 17. Example CRUD flow
To create a simple CRUD page:

1. Define a DB table in your database.
2. Create a `DBModel` class for the table.
3. Add controller actions for listing, creating, editing, deleting.
4. Use views to render the form and list data.

Example model:
```php
class Course extends \app\core\db\DBModel {
    public string $title = '';
    public string $description = '';

    public static function tableName(): string { return 'courses'; }
    public function attributes(): array { return ['title', 'description']; }
    public static function primaryKey(): string { return 'id'; }
    public function rules(): array { return ['title' => [self::RULE_REQUIRED]]; }
}
```

Example save action:
```php
public function addCourseAction(Request $request) {
    $course = new Course();
    if ($request->isPost()) {
        $course->loadData($request->getBody());
        if ($course->validate() && $course->save()) {
            Application::$app->response->redirect('/courses');
            return;
        }
    }
    return $this->render('course_form', ['model' => $course]);
}
```

## 18. Common tips
- Keep `public/` as the web-accessible root.
- Use `Application::$app->response->redirect('/path')` after successful form submits.
- Use `$model->hasError('field')` and `$model->getFirstError('field')` in views.
- Use `setLayout()` when a page should use a different HTML layout.
- Keep business logic in controllers and models; views should stay simple.

## 19. Troubleshooting
- If views fail to load, verify the view file exists under `views/`.
- If routes return 404, check `public/routes.php` and the request path.
- If database connection fails, verify `public/config.php` DSN and credentials.
- If not seeing session data, ensure `session_start()` is running and `public/index.php` is loaded.

## 20. Quick reference
- `public/index.php` — bootstrap application
- `public/routes.php` — app routes
- `public/config.php` — environment settings
- `controllers/` — request handling logic
- `models/` — data structure and validation
- `views/` — UI templates
- `views/layout/` — layout templates
- `core/` — framework internals
- `migrations/` — database setup files

---

Now you can start building pages, forms, and database-backed features with Mandakini Framework.

## DBTable helper (detailed)

`app\core\form\DBTable` is a convenience helper for rendering a styled, paginated HTML table from any `DBModel` without writing custom queries or markup.

- Purpose: produce an index/grid view for a model using the model's metadata and `findAll()` results. The helper handles pagination calculation, basic Bootstrap-based styling, and optional row action links (view/edit/delete).

- Constructor:

    ```php
    new \app\core\form\DBTable(DBModel $model, int $page_id = 1, int $record_no = 50, array $select = [], array $where = [], string $orderby = null)
    ```

    - `$model`: an instance of your `DBModel` (or a class that extends it).
    - `$page_id`: current page (1-based).
    - `$record_no`: rows per page.
    - `$select`: optional list of attributes to display (defaults to all attributes).
    - `$where`: optional associative array of filters applied to the query before fetching rows.
    - `$orderby`: optional order by clause passed to `findAll()`.

- Key methods and usage:
    - `updateUrl(string $updateUrl = '', string $deleteUrl = '', string $viewUrl = '')`
        - Use `{id}` in the URL to inject the row primary key, e.g. `/user/edit/{id}`.
    - `tableUrl(string $tableUrl)`
        - Set base URL for pagination. Supports `{page}` placeholder or falls back to appending `?page=`.
    - `updateSelect(array $select)` — change visible columns.
    - `updateWhere(array $where)` — apply additional WHERE filters.
    - `renderHtml(): string` — returns the HTML string ready to echo in a view. Includes pagination controls when needed.

- Behavior details:
    - Columns: reads `attributes()` from the model to determine columns. If the model defines `labels()`, labels are used as column headers.
    - Data: calls `findAll($where, $orderby, $limit)` on the model to fetch rows for the current page. It runs a `COUNT(*)` query to compute total pages.
    - Actions: if `updateUrl`, `deleteUrl`, or `viewUrl` are set, an Actions column is rendered with links for each row.
    - Pagination: computes offsets and total pages, clamps the current page into a valid range, and renders compact pagination UI.
    - Styling: uses Bootstrap classes and includes helper CSS for a modern grid look; you can override styles in your layout.

- Simple example (controller/view):

    ```php
    use app\core\form\DBTable;

    $userModel = new \models\User(); // extends DBModel
    $table = new DBTable($userModel, $page_id = $_GET['page'] ?? 1, $record_no = 25, ['id','name','email']);
    $table->updateUrl('/user/edit/{id}', '/user/delete/{id}', '/user/view/{id}');
    echo $table->renderHtml();
    ```

This helper is intended for admin lists, index pages and quick CRUD views — use it when you want a ready-made paginated table generated from your model without manual SQL or HTML.
