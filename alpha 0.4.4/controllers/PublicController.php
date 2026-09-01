<?php
namespace app\controllers;

use app\core\Controller;

class PublicController extends Controller {

    public function index() {
        return $this->render('home');
    }
}