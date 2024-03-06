<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PowerSchoolWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        ray('PS hook', $request->all());

        return response('ok');
    }
}
