<?php

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\BaseException;
use App\Exceptions\General\EmailAlreadyVerifiedException;
use App\Exceptions\General\EmailNotVerifiedException;
use App\Exceptions\OTP\InvalidOTPException;
use App\Exceptions\OTP\OTPExpiredException;
use App\Exceptions\OTP\OtpUsedException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(function (Request $r, Throwable $e) {
            return $r->expectsJson() || $r->is('api/*');
        });

        //** For this exception , we don not need to register the message in the log file because it is a business exception and we will handle it in the render method * */
        $exceptions->dontReport([
            ValidationException::class,
            InvalidCredentialsException::class,
            EmailAlreadyVerifiedException::class,
            EmailNotVerifiedException::class,
            OTPExpiredException::class,
            InvalidOTPException::class,
        ]);

        //* This level is used to log the exception in the log file, you can change it to any level you want */
        // $exceptions->level(SystemException::class, LogLevel::CRITICAL);
        // $exceptions->level(BusinessException::class, LogLevel::);

        $exceptions->render(function (AuthenticationException $e, Request $r) {
            return response()->json([
                'message' => 'Error',
                'error' => 'Unauthenticated',
            ], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $r) {
            return response()->json([
                'message' => 'Error Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $r) {
            $previous = $e->getPrevious();

            return response()->json([
                'message' => 'Error',
                'error' => $previous instanceof ModelNotFoundException
                    ? class_basename($previous->getModel()) . ' not found'
                    : 'Resource not found',
            ], 404);
        });

        $exceptions->render(function (BaseException $e, Request $r) {
            return $e->render($r);
        });

        $exceptions->render(function (Throwable $e, Request $r) {
            return response()->json([
                'message' => 'Error',
                'error' => config('app.debug') ? $e->getMessage(): 'Server error',
            ], 500);
        });

        
    })->create();
