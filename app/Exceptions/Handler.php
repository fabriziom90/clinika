<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
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

    public function render($request, Throwable $exception)
    {
        // Se è una richiesta Inertia
        // if ($request->header('X-Inertia')) {

        // 403 (Forbidden)
        if (
            $exception instanceof \Illuminate\Auth\Access\AuthorizationException ||
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 403
        ) {
            $message = $exception->getMessage();

            if (! $message) {
                $message = 'Non sei autorizzato ad accedere a questa sezione.';
            }

            return Inertia::render('Errors/403', [
                'status' => 403,
                'message' => $message,
            ])->toResponse($request)->setStatusCode(403);
        }

        // 404 (Not Found)
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return Inertia::render('Errors/404', [
                'status' => 404,
                'message' => 'Pagina non trovata.',
            ])->toResponse($request)->setStatusCode(404);
        }
        // }

        return parent::render($request, $exception);
    }
}
