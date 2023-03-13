<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = min(intval($request->get('limit', 10)), 1000);

        $students = Student::query();

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'latest') {
            $students->latest();
        } elseif ($sortBy === 'oldest') {
            $students->oldest();
        }

        return $this->respondPagination($request, $students->paginate($limit));
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
            $student = Student::create(['user_id' => $user->id]);

            return $this->respondCreated($student);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load('user');

        return $this->respond($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
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
            
            $student->user->update($input);

            return $this->respond($student);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return $this->respond();
    }
}
