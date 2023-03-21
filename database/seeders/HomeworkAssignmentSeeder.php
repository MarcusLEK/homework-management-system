<?php

namespace Database\Seeders;

use App\Models\HomeworkAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomeworkAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HomeworkAssignment::factory(10)->create();
    }
}
