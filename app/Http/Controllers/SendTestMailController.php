<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TestSmtp;
use Illuminate\Http\Request;

class SendTestMailController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $user->notify(new TestSmtp());

        return response()->json([
            'level' => 'success',
            'message' => __('Check your inbox for the test email.'),
        ]);
    }
}
