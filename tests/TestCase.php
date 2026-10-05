<?php

namespace Tests;

use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $db = $app['db']->connection();
        $working = is_file(base_path('.env')) ? Dotenv::parse(file_get_contents(base_path('.env'))) : [];
        if (! $app->environment('testing') || $app->configurationIsCached()
            || $db->getDriverName() !== 'mysql'
            || $db->getDatabaseName() !== 'skillmatch_testing'
            || ($working['DB_DATABASE'] ?? null) === $db->getDatabaseName()
            || $db->getConfig('url')) {
            throw new \RuntimeException('Tests require uncached MySQL skillmatch_testing, never the working database.');
        }

        return $app;
    }
}
