<?php
namespace app\core;
use app\core\db\Database;
use app\core\db\DBModel;  

class Application {

    public string $layout = 'main';
    public string $appName = 'Mandakini';
    public string $userClass;
    public static string $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public ?Controller $controller = null;
    public static Application $app; 
    public ?UserModel $user;
    public ?View $view;
    public bool $debug = false;

    public Database $db;

    public function __construct($rootPath, array $config) {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db']);
        $this->session = new Session();
        $this->userClass = $config['userClass'] ?? UserModel::class;
        $this->appName = $config['appName'] ?? 'Mandakini';
        $this->view = new View();
        $this->debug = $config['debug'] ?? false;
        $primaryValue = $this->session->get('user');
        if ($this->debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            // Debug: log session and primary user value to runtime for troubleshooting
            $logPath = self::$ROOT_DIR . '/runtime/session_debug.log';
            $log = "[" . date('Y-m-d H:i:s') . "] constructor: primaryValue=" . var_export($primaryValue, true) . "\n";
            $log .= "SESSION=" . var_export($_SESSION, true) . "\n\n";
            @file_put_contents($logPath, $log, FILE_APPEND);
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
 
        if ($primaryValue) {
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
            @file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] loaded user=" . var_export($this->user, true) . "\n\n", FILE_APPEND);
        }else {
            $this->user = null;
        }
        
    }

    public function setController(Controller $controller) {
        $this->controller = $controller;
    }

    public function getController(): ?Controller {
        return $this->controller;
    }

    public function run() {
        try {
            return $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            return $this->router->renderView('_error', [
                'exception' => $e
            ]);
        }
        return $this->router->resolve();
    }

    public function login(UserModel $user) {
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        $this->user = $user;
        // Debug: log login event
        if(self::$app->debug) {
            $logPath = self::$ROOT_DIR . '/runtime/session_debug.log';
            $log = "[" . date('Y-m-d H:i:s') . "] login: set user=" . var_export($primaryValue, true) . "\n";
            $log .= "SESSION=" . var_export($_SESSION, true) . "\n\n";
            @file_put_contents($logPath, $log, FILE_APPEND);
        }
        return true;
    }

    public function logout() {
        $this->user = null;
        $this->session->remove('user');
    }

    public static function isGuest() {
        return !self::$app->user;
    }

    public static function isAdmin(){
        return self::$app->user->category == "admin";
    }

    public function getUser() {
        return $this->user;
    }

    public function getDb() {
        return $this->db;
    }

    public function getUserClass() {
        return $this->userClass;
    }

    public function getAppName() {
        return $this->appName;
    }

}
