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

        // Catch database connection failures and show a friendly error page
        $this->renderable(function (PDOException|QueryException $e, $request) {
            Log::error('Database connection error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'Service temporarily unavailable. Please try again later.',
                    'message' => 'Database connection failed.',
                ], 503);
            }

            return response()->view('errors.db-unavailable', [
                'loginUrl' => route('login'),
            ], 503);
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
