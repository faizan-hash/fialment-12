<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
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
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle CSRF token mismatch (expired session)
        if ($e instanceof TokenMismatchException) {
            // If request is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh and try again.'
                ], 419);
            }
            
            // Flash message for redirect
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }

        return parent::render($request, $e);
    }
} 