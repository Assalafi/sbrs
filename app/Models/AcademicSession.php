<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasUuid, Auditable;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public static function current()
    {
        return self::where('is_current', true)->first();
    }

    public function markAsCurrent(): void
    {
        self::where('is_current', true)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }
}
