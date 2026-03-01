<?php

namespace App\Rules;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $settings = Setting::first();
        $secretKey = $settings->recaptcha_secret_key ?? config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            // No secret key configured — skip validation
            return;
        }

        $g_response = Http::timeout(10)->asForm()->post("https://www.google.com/recaptcha/api/siteverify", [
            'secret'   => $secretKey,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (!$g_response->successful() || !$g_response->json('success')) {
            $fail("reCAPTCHA verification failed. Please try again.");
            return;
        }

        $score = $g_response->json('score');
        if ($score !== null && $score < 0.5) {
            $fail("reCAPTCHA verification failed. Please try again.");
        }
    }
}
