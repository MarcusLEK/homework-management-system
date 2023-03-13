<?php

namespace App\Exceptions;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
        if ($request->is('api*') || $request->is('web*') || $request->expectsJson()) {
            $controller = new ApiController();

            if ($e instanceof NotFoundHttpException) {
                return $controller->respondNotFound('This endpoint does not exist');
            } else if ($e instanceof ModelNotFoundException) {
                return $controller->respondNotFound('The specified resource cannot be found or is no longer available');
            } else if ($e instanceof InternalErrorException) {
                return $controller->respondInternalError();
            } else if ($e instanceof MethodNotAllowedHttpException) {
                return $controller->respondMethodNotAllowed();
            } else if ($e instanceof AuthorizationException || $e instanceof AuthenticationException) {
                return $controller->respondUnauthorizedError();
            } else if ($e instanceof ValidationException) {
                return $controller->respondBadRequestError(implode(" ", $e->validator->errors()->all()));
            } else if ($e instanceof UnauthorizedException) {
                return $controller->respondUnauthorizedError();
            } elseif ($e->getTrace()[0]['class'] == 'Lcobucci\JWT\Signer\Hmac') {
                //JWT throws a weird error if the key is invalid - no other proper way to check it other than this
                return $controller->respondUnauthorizedError('Invalid token');
            } else {
                $controller->addDebugInfo($this->convertExceptionToArray($e));
                return $controller->respondInternalError();
            }
        }
        return parent::render($request, $e);
    }
}
