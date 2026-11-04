<?php

use Symfony\Component\Process\Process;

require __DIR__.'/browser-environment.php';
require __DIR__.'/../vendor/autoload.php';
$server = new Process([PHP_BINARY, 'artisan', 'serve', '--host=127.0.0.1', '--port=8011', '--no-reload'], dirname(__DIR__), $browserEnvironment);
$server->setTimeout(null);
exit($server->run(fn ($type, $buffer) => print ($buffer)));
