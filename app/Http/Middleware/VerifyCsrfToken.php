<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // MPESA API callbacks - these come from Safaricom, not browser forms
        'api/validation',
        'api/transaction/confirmation',
        'api/stk/confirmation',
        'api/*',
    ];
}
