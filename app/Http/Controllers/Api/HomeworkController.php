<?php

namespace App\Http\Controllers\Api;

use App\Models\Homework;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = min(intval($request->get('limit', 10)), 1000);

        $homeworks = Homework::query();
        if (!empty(Auth::user()->teacher)) {
            $homeworks = $homeworks->where('teacher_id', Auth::user()->teacher->id);
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
            'title' => 'required|min:3',
            'description' => 'nullable|min:3'
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        try {
            
            $homework = Homework::create($input + ['teacher_id' => Auth::user()->teacher->id]);

            return $this->respondCreated($homework);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Homework $homework)
    {
        return $this->respond($homework);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Homework $homework)
    {
        $input = $request->input();

        $validator = Validator::make($input, [
            'title' => 'required|min:3',
            'description' => 'nullable|min:3'
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequestError('Error: ' . implode(" ", $validator->errors()->all()));
        }

        try {
            
            $homework->update($input);

            return $this->respond($homework);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->respondInternalError();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Homework $homework)
    {
        $homework->delete();

        return $this->respond();
    }
}
