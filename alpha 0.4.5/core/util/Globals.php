<?php

namespace app\core\util;
require_once __DIR__ . '/../../public/config.php';

class Globals {
    public static function getConfig() {
        return $config ?? require __DIR__ . '/../../public/config.php';
    }
}