<?php

namespace Database\Factories;

use App\Models\Homework;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HomeworkAssignment>
 */
class HomeworkAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $homework = Homework::inRandomOrder()->first();
        return [
            'teacher_id' => $homework->teacher_id,
            'homework_id' => $homework->id,
            'student_id' => Student::inRandomOrder()->first(),
        ];
    }
}
