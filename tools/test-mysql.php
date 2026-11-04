<?php

// Only for a NEW disposable database on a dedicated, locally initialized test server.
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Process\Process;

$port = (int) (getenv('FOODIE_TEST_MYSQL_PORT') ?: 33079);
if ($port === 3306 || $port < 1024) {
    fwrite(STDERR, "Refusing normal database port. Use an isolated test server.\n");
    exit(1);
}
$secretFile = __DIR__.'/.runtime/mysql-password';
$password = is_file($secretFile) ? file_get_contents($secretFile) : '';
$pdo = new PDO('mysql:host=127.0.0.1;port='.$port.';charset=utf8mb4', 'root', $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
if ($password === '') {
    $password = bin2hex(random_bytes(32));
    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY ".$pdo->quote($password));
    file_put_contents($secretFile, $password);
    @chmod($secretFile, 0600);
}
$name = 'foodie_test_'.bin2hex(random_bytes(5));
$pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
file_put_contents(__DIR__.'/.runtime/last-mysql-database', $name);
$env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => (string) $port, 'DB_DATABASE' => $name, 'DB_USERNAME' => 'root', 'DB_PASSWORD' => $password, 'DB_URL' => '', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync'];
$process = new Process([PHP_BINARY, 'artisan', 'test', ...array_slice($argv, 1)], dirname(__DIR__), $env);
$process->setTimeout(300);
$code = $process->run(fn ($type, $buffer) => print ($buffer));
echo "Isolated test database retained: {$name} on 127.0.0.1:{$port}\n";
exit($code);
