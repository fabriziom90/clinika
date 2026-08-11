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
        // 419 - Page Expired
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 419) {

            return Inertia::render('Errors/419', [
                'status' => 419,
                'message' => 'La sessione è scaduta o la richiesta non è più valida.',
            ])->toResponse($request)->setStatusCode(419);
        }

        // 429 - Too Many Requests
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 429) {

            return Inertia::render('Errors/429', [
                'status' => 429,
                'message' => 'Hai effettuato troppe richieste in un breve periodo di tempo.',
            ])->toResponse($request)->setStatusCode(429);
        }

        // 503 - Service Unavailable
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 503) {

            return Inertia::render('Errors/503', [
                'status' => 503,
                'message' => 'Clinika non è momentaneamente disponibile.',
            ])->toResponse($request)->setStatusCode(503);
        }

        // 500 - Internal Server Error
        if (app()->environment('local')) {
            return parent::render($request, $exception);
        }

        return Inertia::render('Errors/500', [
            'status' => 500,
            'message' => 'Si è verificato un errore durante l\'elaborazione della richiesta.',
        ])->toResponse($request)->setStatusCode(500);

    }
}
