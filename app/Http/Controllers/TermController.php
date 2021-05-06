<?php

namespace App\Http\Controllers;

use App\Http\Resources\TermResource;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $terms = $request->school()
            ->terms()
            ->orderBy('start_year', 'desc')
            ->orderBy('starts_at')
            ->orderBy('portion')
            ->get();

        return TermResource::collection($terms);
    }
}
