<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Config;

final class ConfigTest extends TestCase
{
    public function test_config_value_can_be_retrieved()
    {
        $this->assertNotNull(Config::get('app.name'));
    }

    public function test_config_default_value()
    {
        $this->assertEquals('default', Config::get('app.unknown', 'default'));
    }
}
