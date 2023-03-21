<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeworkAssignment extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;

    protected $fillable = [
        'teacher_id', 'homework_id', 'student_id', 'status'
    ];

    public function student(): BelongsTo
    {
        return $this->BelongsTo(Student::class);
    }
}
