<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\User;
use app\models\LoginForm;
use app\core\Application;

class AuthController extends Controller {
    public string $id = 'Auth';


    public function __construct() {
        $this->setMiddleware(new \app\core\middlewares\AuthMiddleware(['profile','register']));
    }


    public function login(Request $request) {

        $model = new LoginForm();
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            if ($model->validate() && $model->login()) {
                Application::$app->response->redirect('/');
                return;
            }
        }
         $this->setLayout('itdlh_landing_new');
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function logout() {
        Application::$app->logout();
        Application::$app->response->redirect('/');
    }

    public function register(Request $request) {
        $errors = [];
        $user = new User();
        if ($request->isPost()) {
           
            $user->loadData($request->getBody());

            if($user->validate() and $user->save()) {
                Application::$app->session->setFlash('success', 'Thanks for registering');
                Application::$app->response->redirect('/');
            }

            
            return $this->render('register', [
                'model' => $user,
            ]);
            
        }
         $this->setLayout('itdlh_landing_new');
        return $this->render('register', [
            'model' => $user,
        ]);
    }

    public function profile() {
        $user = Application::$app->user;
         $this->setLayout('itdlh_landing_new');
        return $this->render('profile', [
            'user' => $user, 'title' => 'Profile',
        ]);
    }
}