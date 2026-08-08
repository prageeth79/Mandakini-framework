<?php
namespace app\core\exceptions;

class ForbiddenException extends \Exception {
    protected $code = 403;
    protected $message = 'You are not allowed to access this page';
}