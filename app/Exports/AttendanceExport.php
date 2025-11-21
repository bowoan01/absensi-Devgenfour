<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection(): Collection
    {
        return $this->query->get()->map(function ($attendance) {
            return [
                'Student' => $attendance->student?->full_name,
                'Student ID' => $attendance->student?->student_id_code,
                'Date' => optional($attendance->date)->format('Y-m-d'),
                'Check In' => optional($attendance->check_in_at)->toDateTimeString(),
                'Check Out' => optional($attendance->check_out_at)->toDateTimeString(),
                'Status' => ucfirst($attendance->status),
                'Note' => $attendance->note,
            ];
        });
    }

    public function headings(): array
    {
        return ['Student', 'Student ID', 'Date', 'Check In', 'Check Out', 'Status', 'Note'];
    }
}
