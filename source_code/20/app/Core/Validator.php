<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(string $field, $value)
    {
        if (empty(trim((string)$value))) {
            $this->errors[$field][] = 'This field is required';
        }
    }

    public function email(string $field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Invalid email address';
        }
    }

    public function min(string $field, $value, int $length)
    {
        if (strlen((string)$value) < $length) {
            $this->errors[$field][] = "Minimum {$length} characters required";
        }
    }

    public function confirmed(string $field, $value, $confirmation)
    {
        if ($value !== $confirmation) {
            $this->errors[$field][] = 'Password confirmation does not match';
        }
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function all()
    {
        $messages = [];

        foreach ($this->errors as $fieldErrors) {
            $messages[] = $fieldErrors[0]; // first error only
        }

        return $messages;
    }
}
