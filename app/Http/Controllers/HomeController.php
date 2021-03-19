<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request)
    {
        $title = __('Dashboard');

        return inertia('Index', [
            'title' => $title,
        ])->withViewData(compact('title'));
    }
}
