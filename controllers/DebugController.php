<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Application;

class DebugController extends Controller {
    public function session() {
        header('Content-Type: text/plain');
        echo "--- \\$_SESSION ---\n";
        echo print_r($_SESSION, true);
        echo "\n\n--- Application::user ---\n";
        echo print_r(Application::$app->user, true);
        echo "\n";
    }

    public function tables() {
        // show a demo table for users using DBTable helper
        $userModel = new \app\models\User();
        $page = intval($_GET['page'] ?? 1);
        $dbTable = new \app\core\form\DBTable($userModel, $page, 25);
        $dbTable->updateUrl('/user/edit/{id}', '/user/delete/{id}', '/user/view/{id}');
        return $this->render('debug_tables', ['dbtable' => $dbTable]);
    }
}
