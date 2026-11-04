<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();
        $safe = $driver === 'sqlite' && $database === ':memory:';
        if (in_array($driver, ['mysql', 'mariadb'])) {
            $safe = str_starts_with($database, 'foodie_test_') && $connection->getConfig('host') === '127.0.0.1' && (int) $connection->getConfig('port') !== 3306;
        }
        if (! $safe) {
            throw new \RuntimeException('Refusing destructive test refresh outside an isolated memory or foodie_test_* database. Use tools/test-mysql.php.');
        }

        return parent::setUpTraits();
    }
}
