<?php

namespace Modules\Task\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaskStatusRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {}
}
