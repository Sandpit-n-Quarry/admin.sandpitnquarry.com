<?php

namespace App\Exports;

use App\Models\Transporter;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransportersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Transporter::with(['owner']);

        // Filter by search term (case-insensitive)
        if ($this->request->has('search') && !empty($this->request->search)) {
            $searchTerm = $this->request->search;
            $escapedSearchTerm = strtolower(addcslashes($searchTerm, '%_'));
            $pattern = '%' . $escapedSearchTerm . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
            });
        }

        // Filter by type
        if ($this->request->has('type') && $this->request->type != 'Type') {
            $query->where('type', $this->request->type);
        }

        return $query->orderBy('id', 'desc');
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'ID',
            'Transporter Name',
            'Registration Number',
            'Type',
            'Owner Name',
            'Owner Email',
            'Join Date',
            'Created At',
        ];
    }

    /**
     * Map each transporter to an array for export
     */
    public function map($transporter): array
    {
        return [
            $transporter->id,
            $transporter->name ?? 'N/A',
            $transporter->registration_number ?? 'N/A',
            $transporter->type ?? 'N/A',
            $transporter->owner ? $transporter->owner->name : 'N/A',
            $transporter->owner ? $transporter->owner->email : 'N/A',
            $transporter->created_at ? $transporter->created_at->format('M d, Y') : 'N/A',
            $transporter->created_at ? $transporter->created_at->format('M d, Y H:i:s') : 'N/A',
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
