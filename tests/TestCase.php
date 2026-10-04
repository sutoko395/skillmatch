<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $db = $app['db']->connection();
        if (!$app->environment('testing') || $app->configurationIsCached()
            || $db->getDriverName() !== 'mysql'
            || $db->getDatabaseName() !== 'skillmatch_testing'
            || $db->getConfig('url')) {
            throw new \RuntimeException('Tests require uncached MySQL skillmatch_testing, never the working database.');
        }
        return $app;
    }
}
