<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Multitenancy\Exceptions\NoCurrentTenant;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<string>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NoCurrentTenant $e) {
            abort(404);
        });
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if (
            ! app()->environment('local') &&
            in_array($response->status(), [500, 503, 404, 403])
        ) {
            $title = __('Error');

            return inertia('Error', [
                'status' => $response->status(),
                'title' => $title,
            ])
                ->withViewData(compact('title'))
                ->toResponse($request)
                ->setStatusCode($response->status());
        }

        return $response;
    }
}
