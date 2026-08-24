<?php

namespace app\core\a0_2\exceptions;

class NotFoundException extends \Exception {
    protected $code = 404;
    protected $message = 'The page you are looking for was not found';
}