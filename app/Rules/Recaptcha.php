<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $g_response = Http::timeout(10)->asForm()->post("https://www.google.com/recaptcha/api/siteverify", [
            'secret'=>config('services.recaptcha.secret_key'),
            'response'=>$value,
            'remoteip'=>request()->ip(),
        ]);
        if (!$g_response->successful() || !$g_response->json('success')) {
            $fail("The {$attribute} is invalid.");
            return;
        }
        $score = $g_response->json('score');
        if ($score !== null && $score < 0.5) {
            $fail("The {$attribute} verification failed. Please try again.");
        }
    }
}
