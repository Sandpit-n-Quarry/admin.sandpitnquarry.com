<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the query based on filters
     */
    public function query()
    {
        $query = User::query();
        
        // Handle search (case-insensitive)
        if ($this->request->has('search') && !empty($this->request->search)) {
            $searchTerm = strtolower($this->request->search);
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]);
            });
        }
        
        // Handle status filter
        if ($this->request->has('status') && $this->request->status !== 'Status') {
            if ($this->request->status === 'Active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($this->request->status === 'Inactive') {
                $query->whereNull('email_verified_at');
            }
        }
        
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Department',
            'Designation',
            'Status',
            'Join Date',
            'Email Verified At',
        ];
    }

    /**
     * Map each user to an array for export
     */
    public function map($user): array
    {
        $status = $user->email_verified_at ? 'Active' : 'Inactive';

        return [
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->department ?? 'N/A',
            $user->designation ?? 'N/A',
            $status,
            $user->created_at ? $user->created_at->format('M d, Y') : 'N/A',
            $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i:s') : 'N/A',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A90E2']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
