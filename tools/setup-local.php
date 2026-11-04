<?php

// Optional local workspace bootstrap. Uses ONLY this task's disposable loopback test cluster.
require __DIR__.'/../vendor/autoload.php';

$root = dirname(__DIR__);
$envPath = $root.'/.env';
$contents = file_get_contents($envPath);
if (preg_match('/^DB_DATABASE=/m', $contents)) {
    fwrite(STDERR, "Existing database configuration preserved. Configure manually or use the existing database.\n");
    exit(1);
}
$passwordFile = __DIR__.'/.runtime/mysql-password';
if (! is_file($passwordFile)) {
    fwrite(STDERR, "Initialize the isolated test server first; see docs/operations.md.\n");
    exit(1);
}
$pdo = new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4', 'root', file_get_contents($passwordFile), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$suffix = bin2hex(random_bytes(4));
$name = 'foodie_local_'.$suffix;
$username = 'foodie_'.$suffix;
$password = bin2hex(random_bytes(32));
$pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('CREATE USER '.$pdo->quote($username)."@'localhost' IDENTIFIED BY ".$pdo->quote($password));
$pdo->exec('GRANT ALL ON `'.$name.'`.* TO '.$pdo->quote($username)."@'localhost'");
$values = ['DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '33079', 'DB_DATABASE' => $name, 'DB_USERNAME' => $username, 'DB_PASSWORD' => $password];
foreach ($values as $key => $value) {
    if (preg_match('/^'.preg_quote($key, '/').'=/m', $contents)) {
        $contents = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $key.'='.$value, $contents);
    } else {
        $contents .= "\n{$key}={$value}";
    }
}
file_put_contents($envPath, $contents."\n");
@chmod($envPath, 0600);
file_put_contents(__DIR__.'/.runtime/local-database', $name);
echo "Configured NEW empty local database {$name} on 127.0.0.1:33079. No admin password was created.\n";
