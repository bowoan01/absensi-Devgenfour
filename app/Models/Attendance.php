<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';

    protected $table = 'attendance';
    protected $fillable = [
        'student_id',
        'date',
        'check_in_at',
        'check_out_at',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }
        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    public function isCheckedIn(): bool
    {
        return !is_null($this->check_in_at);
    }

    public function isCheckedOut(): bool
    {
        return !is_null($this->check_out_at);
    }

    public static function determineStatusForCheckIn(Carbon $now, string $cutoff = '10:00'): string
    {
        $cutoffTime = Carbon::parse($cutoff, $now->timezone)->setDate($now->year, $now->month, $now->day);
        return $now->greaterThan($cutoffTime) ? self::STATUS_LATE : self::STATUS_PRESENT;
    }
}
