<?php

$runtime = __DIR__.'/.runtime';
if (! is_dir($runtime)) {
    mkdir($runtime, 0700, true);
}
$browserEnvironment = [
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_URL' => 'http://127.0.0.1:8011',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $runtime.'/browser.sqlite', 'DB_URL' => '',
    'FOODIE_MEDIA_ROOT' => $runtime.'/browser-media', 'SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'file',
    'SESSION_COOKIE' => 'foodie_browser_qa', 'FOODIE_SESSION_PATH' => $runtime.'/browser-sessions', 'FOODIE_CACHE_PATH' => $runtime.'/browser-cache',
    'QUEUE_CONNECTION' => 'sync',
    'FOODIE_BROWSER_QA' => 'true',
];
foreach (['browser-sessions', 'browser-cache'] as $directory) {
    if (! is_dir($runtime.'/'.$directory)) {
        mkdir($runtime.'/'.$directory, 0700, true);
    }
}
foreach ($browserEnvironment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
