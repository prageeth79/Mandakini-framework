<?php
namespace app\core\cli;

interface CommandInterface {
    public function execute(array $args): int;
    public function getDescription(): string;
}