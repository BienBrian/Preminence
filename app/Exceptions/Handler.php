<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Catch database CONNECTION failures and show a friendly error page
        $this->renderable(function (PDOException|QueryException $e, $request) {
            $msg = $e->getMessage();
            $code = (int) $e->getCode();

            // 1) Connection failures → 503
            $connectionCodes = [2002, 2006, 1045, 1049, 2003, 2005];
            $isConnectionError = in_array($code, $connectionCodes)
                || str_contains($msg, 'SQLSTATE[HY000] [2002]')
                || str_contains($msg, 'Connection refused')
                || str_contains($msg, 'No such file or directory');

            if ($isConnectionError) {
                Log::error('Database connection error: ' . $msg);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Service temporarily unavailable. Please try again later.',
                    ], 503);
                }

                return response()->view('errors.db-unavailable', [
                    'loginUrl' => route('login'),
                ], 503);
            }

            // 2) Integrity constraint violations (duplicate key, FK, etc.) → friendly error
            if (str_contains($msg, 'SQLSTATE[23000]')) {
                Log::warning('Database constraint violation: ' . $msg);

                $friendly = 'A record with this information already exists. Please check your input and try again.';

                // Try to extract a more specific field name from "Duplicate entry ... for key 'users.users_phone_unique'"
                if (preg_match("/Duplicate entry .+ for key '.*?_(\w+)_unique'/", $msg, $m)) {
                    $field = str_replace('_', ' ', $m[1]);
                    $friendly = "This {$field} is already in use. Please use a different one.";
                }

                if ($request->expectsJson()) {
                    return response()->json(['error' => $friendly], 422);
                }

                return back()->withInput()->withErrors(['error' => $friendly]);
            }

            // 3) Unknown column / missing table → friendly error in production
            if (str_contains($msg, 'SQLSTATE[42S22]') || str_contains($msg, 'SQLSTATE[42S02]')) {
                Log::error('Database schema error: ' . $msg);

                if (!config('app.debug')) {
                    $friendly = 'Something went wrong. Please contact support if this persists.';

                    if ($request->expectsJson()) {
                        return response()->json(['error' => $friendly], 500);
                    }

                    return back()->withInput()->withErrors(['error' => $friendly]);
                }
            }

            // All other DB errors: let Laravel handle normally (shows debug page in dev, generic 500 in prod)
            return null;
        });
    }

    /**
     * Redirect unauthenticated users to the login page.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'))
            ->with('error', 'Your session has expired. Please log in again.');
    }
}
