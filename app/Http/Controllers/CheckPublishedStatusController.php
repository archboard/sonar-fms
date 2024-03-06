<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CheckPublishedStatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $published = $user->selectedInvoices()
            ->whereNull('published_at')
            ->doesntExist();

        return response()
            ->json(compact('published'));
    }
}
