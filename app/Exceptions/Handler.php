<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return redirect()->guest($exception->redirectTo($request) ?? route('login'));
        }

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

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return Inertia::render('Errors/404', [
                'status' => 404,
                'message' => 'Pagina non trovata.',
            ])->toResponse($request)->setStatusCode(404);
        }

        if (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 419
        ) {
            return Inertia::render('Errors/419', [
                'status' => 419,
                'message' => 'La sessione è scaduta o la richiesta non è più valida.',
            ])->toResponse($request)->setStatusCode(419);
        }

        if (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 429
        ) {
            return Inertia::render('Errors/429', [
                'status' => 429,
                'message' => 'Hai effettuato troppe richieste in un breve periodo di tempo.',
            ])->toResponse($request)->setStatusCode(429);
        }

        if (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() === 503
        ) {
            return Inertia::render('Errors/503', [
                'status' => 503,
                'message' => 'Clinika non è momentaneamente disponibile.',
            ])->toResponse($request)->setStatusCode(503);
        }

        if (app()->environment(['local', 'testing'])) {
            return parent::render($request, $exception);
        }

        return Inertia::render('Errors/500', [
            'status' => 500,
            'message' => 'Si è verificato un errore durante l\'elaborazione della richiesta.',
        ])->toResponse($request)->setStatusCode(500);
    }
}
