<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Student;
use App\Traits\SendsApiResponses;
use Illuminate\Http\Request;

class StudentCommentController extends Controller
{
    use SendsApiResponses;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Student $student)
    {
        $comments = $student->comments()
            ->orderBy('created_at')
            ->with('commentator')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Student $student
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Student $student)
    {
        $data = $request->validate(['comment' => ['required', 'string']]);

        $comment = $student->commentAsUser($request->user(), $data['comment']);

//        $comment->load('commentator');
//        return $this->success(
//            __('Comment created successfully.'),
//            [
//                'comment' => $comment->toResource(),
//            ]
//        );

        session()->flash('success', __('Comment created successfully.'));

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Comment $comment)
    {
        return $comment->toResource();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Student $student, Comment $comment)
    {
        abort_if(
            !$request->user()->ownsComment($comment),
            403,
            __("You don't have permission to edit this comment.")
        );

        $data = $request->validate(['comment' => ['required', 'string']]);
        $comment->update($data);

        session()->flash('success', __('Comment updated successfully.'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Student $student, Comment $comment)
    {
        abort_if(
            !$request->user()->ownsComment($comment),
            403,
            __("You don't have permission to edit this comment.")
        );

        $comment->delete();

        return $this->success(__('Comment deleted successfully.'));
    }
}
