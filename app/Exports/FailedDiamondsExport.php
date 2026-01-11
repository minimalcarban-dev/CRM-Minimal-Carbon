<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedDiamondsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        // Normalize associative input rows to match headings order
        $headings = $this->headings();
        $normalized = [];
        foreach ($this->rows as $row) {
            $out = [];
            foreach ($headings as $h) {
                // Map keys to expected headings; allow both spaced and underscored keys
                $key = $h;
                if (!array_key_exists($key, $row)) {
                    $alt = strtolower(str_replace(' ', '_', $h));
                    $key = array_key_exists($alt, $row) ? $alt : $key;
                }
                $out[] = $row[$key] ?? '';
            }
            $normalized[] = $out;
        }
        return $normalized;
    }

    public function headings(): array
    {
        return [
            'Original Row',
            'Error Description',
            'lot_no',
            'sku',
            'material',
            'cut',
            'clarity',
            'color',
            'shape',
            'measurement',
            'weight',
            'per_ct',
            'purchase_price',
            'margin',
            'listing_price',
            'shipping_price',
            'purchase_date',
            'sold_out_date',
            'is_sold_out',
            'duration_days',
            'duration_price',
            'sold_out_price',
            'profit',
            'sold_out_month',
            'barcode_number',
            'description',
            'note',
            'diamond_type',
            'admin_id',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
            'A' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7']
            ]],
            'B' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEE2E2']
            ]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 50,
            'C' => 15,
            'D' => 15,
        ];
    }
}
