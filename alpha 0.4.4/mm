#!/usr/bin/env php
<?php

use app\core\Application;
use app\core\cli\Console;
use app\core\db\Database;
use app\core\cli\commands\MakeModelCommand;
use app\core\cli\commands\MakeControllerCommand;
use app\core\cli\commands\MakeViewCommand;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/public/config.php';


// Instantiate Application environment & Database


$app = new Application(__DIR__, $config);

// Boot CLI runner
$console = new Console();
$console->register('make:model', new MakeModelCommand());
$console->register('make:view', new MakeViewCommand());
$console->register('make:controller', new MakeControllerCommand());

// Run command pipeline
exit($console->run($argv));