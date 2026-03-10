<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasUuid;

    protected $fillable = [
        'student_id',
        'course_id',
        'academic_session_id',
        'semester',
        'ca_score',
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'uploaded_by',
    ];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function calculateGrade(float $totalScore): string
    {
        if ($totalScore >= 70) return 'A';
        if ($totalScore >= 60) return 'B';
        if ($totalScore >= 50) return 'C';
        if ($totalScore >= 45) return 'D';
        if ($totalScore >= 40) return 'E';
        return 'F';
    }

    public static function getRemark(string $grade): string
    {
        return in_array($grade, ['A', 'B', 'C', 'D', 'E']) ? 'Pass' : 'Fail';
    }
}
