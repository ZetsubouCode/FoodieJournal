<?php

require __DIR__.'/browser-environment.php';
foreach (['APP_URL' => 'http://127.0.0.1:8012', 'DB_DATABASE' => __DIR__.'/.runtime/cinematic.sqlite', 'FOODIE_MEDIA_ROOT' => __DIR__.'/.runtime/cinematic-media'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if (! is_file(__DIR__.'/.runtime/cinematic.sqlite')) {
    throw new RuntimeException('Run php tools/prepare-cinematic-qa.php first.');
}
chdir(__DIR__.'/../public');
passthru('"'.PHP_BINARY.'" -S 127.0.0.1:8012 -t "'.__DIR__.'/../public" "'.__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"');
