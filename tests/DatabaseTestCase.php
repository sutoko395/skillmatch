<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class DatabaseTestCase extends TestCase
{
    use DatabaseTransactions;
}
