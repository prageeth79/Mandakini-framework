<?php
namespace app\core\middlewares;
use app\core\Application;
use app\core\exceptions\ForbiddenException;
use app\models\User;

class AuthMiddleware extends BaseMiddleware {

    public array $actions = [];

    public function __construct($actions = []) {
        $this->actions = $actions;
    }

    public function execute() {
       // if (Application::$app->user) {
       //     return true;
       // }
        $user = new User();
        $user = $user::findOne([$user::primaryKey() => Application::$app->session->get('user') ]);
       if(Application::isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }elseif(Application::$app->user->category != 'instructor' && Application::$app->user->category != 'admin'){
            if(empty($this->actions) || in_array(Application::$app->controller->action, $this->actions) ){
                if(Application::$app->controller->action == 'add_courses'){
                    throw new ForbiddenException();
                }
            }

        }elseif(Application::$app->user->category != 'admin'){
                if(Application::$app->controller->action == 'register'){
                    throw new ForbiddenException();
                }
        }
                
    }

        //Application::$app->response->redirect('/login');
        //return false;
}
