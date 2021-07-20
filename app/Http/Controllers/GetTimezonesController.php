<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Illuminate\Http\Request;
use IntlTimeZone;

class GetTimezonesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        return response()->json(timezones());
    }
}
