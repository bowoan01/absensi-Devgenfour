<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'student_id',
        'date',
        'check_in_at',
        'check_out_at',
        'status',
        'note',
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        if ($start) {
            $query->whereDate('date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('date', '<=', $end);
        }
        return $query;
    }

    public static function determineStatus(Carbon $checkIn, string $cutoff = '10:00'): string
    {
        $cutoffTime = Carbon::parse($checkIn->toDateString() . ' ' . $cutoff, $checkIn->getTimezone());
        return $checkIn->greaterThan($cutoffTime) ? self::STATUS_LATE : self::STATUS_PRESENT;
    }
}
