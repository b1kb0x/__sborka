<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $storagePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'__sborka-test-storage';

        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $this->app->useStoragePath($storagePath);

        $compiledPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'__sborka-test-views';

        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0777, true);
        }

        config()->set('view.compiled', $compiledPath);
    }
}
