<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\SendsApiResponses;
use Illuminate\Http\Request;

class RemoveStudentSelectionController extends Controller
{
    use SendsApiResponses;

    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user->studentSelections()->delete();

        return $this->success(__('Selection removed successfully.'));
    }
}
