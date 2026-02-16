<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function test_testing_env_loaded()
    {
        $this->assertEquals('testing', $_ENV['APP_ENV']);
        $this->assertStringContainsString('test_', $_ENV['DB_NAME']);
    }
}
