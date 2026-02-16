<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

final class ValidatorTest extends TestCase
{
    public function test_required_validation_fails()
    {
        $validator = new Validator();
        $validator->required('email', '');

        $this->assertTrue($validator->fails());
    }

    public function test_email_validation_passes()
    {
        $validator = new Validator();
        $validator->email('email', 'test@example.com');

        $this->assertFalse($validator->fails());
    }

    public function test_min_length_validation()
    {
        $validator = new Validator();
        $validator->min('password', '123', 6);

        $this->assertTrue($validator->fails());
    }
}
