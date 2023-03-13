<?php

namespace App\Http\Controllers\Api;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = min(intval($request->get('limit', 10)), 1000);

        $teachers = Teacher::query();

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'latest') {
            $teachers->latest();
        } elseif ($sortBy === 'oldest') {
            $teachers->oldest();
        }

        return $this->respondPagination($request, $teachers->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'name' => 'required|min:3',
            'username' => 'required|min:3',
            'password' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        try {
            
            $user = User::create($input);
            $teacher = teacher::create(['user_id' => $user->id]);

            return $this->respondCreated($teacher);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(teacher $teacher)
    {
        $teacher->load('user');

        return $this->respond($teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'name' => 'required|min:3',
            'username' => 'required|min:3',
            'password' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        try {
            
            $teacher->user->update($input);

            return $this->respond($teacher);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return $this->respond();
    }
}
