<?php

namespace App\Http\Controllers\Api;

use App\Models\HomeworkAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HomeworkAssignmentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = min(intval($request->get('limit', 10)), 1000);

        $homeworks = HomeworkAssignment::query();
        if (!empty(Auth::user()->teacher)) {
            $homeworks->where('teacher_id', Auth::user()->teacher->id);
        } else {
            $homeworks->where('student_id', Auth::user()->student->id);
        }

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'latest') {
            $homeworks->latest();
        } elseif ($sortBy === 'oldest') {
            $homeworks->oldest();
        }

        return $this->respondPagination($request, $homeworks->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'homework_id' => 'required|exists:teachers,id',
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        $homeworkAssignment = HomeworkAssignment::where('homework_id', $input['homework_id'])->where('student_id', $input['student_id'])->first();
        if (!empty($homeworkAssignment)) {
            return $this->respondBadRequestError('homework already assigned to student');
        }

        try {
            
            $homeworkAssignment = HomeworkAssignment::create($input + ['teacher_id' => Auth::user()->teacher->id]);

            return $this->respondCreated($homeworkAssignment);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HomeworkAssignment $homeworkAssignment)
    {
        return $this->respond($homeworkAssignment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HomeworkAssignment $homeworkAssignment)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'homework_id' => 'required|exists:homeworks,id',
            'status' => 'required|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        try {
            
            $homeworkAssignment->update($input);

            return $this->respondCreated($homeworkAssignment);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeworkAssignment $homeworkAssignment)
    {
        $homeworkAssignment->delete();

        return $this->respond();
    }
}
