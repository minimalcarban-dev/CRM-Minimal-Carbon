<?php

namespace App\Exports;

use App\Models\Diamond;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DiamondsExport implements FromCollection, WithHeadings, WithMapping, WithChunkReading
{
    protected $diamonds;
    protected $filters;

    /**
     * Constructor accepts either a Collection (from job) or Request (direct export)
     * @param Collection|Request|null $data
     */
    public function __construct($data = null)
    {
        if ($data instanceof Collection) {
            // From job - already have the diamonds collection
            $this->diamonds = $data;
            $this->filters = [];
        } elseif ($data instanceof Request) {
            // From direct export - need to query
            $this->diamonds = null;
            $this->filters = $data->all();
        } else {
            // No data - export all
            $this->diamonds = null;
            $this->filters = [];
        }
    }

    public function collection()
    {
        // If diamonds already provided (from job), return them
        if ($this->diamonds !== null) {
            return $this->diamonds;
        }

        // Otherwise query based on filters
        $query = Diamond::with(['assignedAdmin', 'assignedByAdmin'])
            ->orderBy('id', 'desc');

        // Text filters
        if (!empty($this->filters['sku'])) {
            $query->where('sku', 'like', '%' . $this->filters['sku'] . '%');
        }
        if (!empty($this->filters['lot_no'])) {
            $query->where('lot_no', 'like', '%' . $this->filters['lot_no'] . '%');
        }

        // Enum filters
        if (!empty($this->filters['status'])) {
            $query->where('is_sold_out', $this->filters['status']);
        }
        if (!empty($this->filters['shape'])) {
            $query->where('shape', $this->filters['shape']);
        }
        if (!empty($this->filters['cut'])) {
            $query->where('cut', $this->filters['cut']);
        }
        if (!empty($this->filters['clarity'])) {
            $query->where('clarity', $this->filters['clarity']);
        }
        if (!empty($this->filters['color'])) {
            $query->where('color', $this->filters['color']);
        }
        if (!empty($this->filters['material'])) {
            $query->where('material', $this->filters['material']);
        }
        if (!empty($this->filters['diamond_type'])) {
            $query->where('diamond_type', $this->filters['diamond_type']);
        }

        // Numeric range filters
        if (isset($this->filters['min_price']) && $this->filters['min_price'] !== '') {
            $query->where('purchase_price', '>=', (float) $this->filters['min_price']);
        }
        if (isset($this->filters['max_price']) && $this->filters['max_price'] !== '') {
            $query->where('purchase_price', '<=', (float) $this->filters['max_price']);
        }
        if (isset($this->filters['min_weight']) && $this->filters['min_weight'] !== '') {
            $query->where('weight', '>=', (float) $this->filters['min_weight']);
        }
        if (isset($this->filters['max_weight']) && $this->filters['max_weight'] !== '') {
            $query->where('weight', '<=', (float) $this->filters['max_weight']);
        }

        // Admin filter (only for super admins via UI, safe to include)
        if (!empty($this->filters['admin_id'])) {
            $query->where('admin_id', (int) $this->filters['admin_id']);
        }

        return $query->get();
    }

    public function map($diamond): array
    {
        return [
            $diamond->id,
            $diamond->lot_no,
            $diamond->sku,
            $diamond->material,
            $diamond->cut,
            $diamond->clarity,
            $diamond->color,
            $diamond->shape,
            $diamond->measurement,
            $diamond->weight,
            $diamond->per_ct,
            $diamond->purchase_price,
            $diamond->margin,
            $diamond->listing_price,
            $diamond->shipping_price,
            $diamond->purchase_date ? $diamond->purchase_date->format('Y-m-d') : '',
            $diamond->sold_out_date ? $diamond->sold_out_date->format('Y-m-d') : '',
            $diamond->is_sold_out,
            $diamond->duration_days,
            $diamond->duration_price,
            $diamond->sold_out_price,
            $diamond->profit,
            $diamond->sold_out_month,
            $diamond->barcode_number,
            $diamond->description,
            $diamond->note,
            $diamond->diamond_type,
            $diamond->assignedAdmin ? $diamond->assignedAdmin->name : '',
            $diamond->assignedByAdmin ? $diamond->assignedByAdmin->name : '',
            $diamond->assigned_at ? $diamond->assigned_at->format('Y-m-d H:i:s') : '',
            is_array($diamond->multi_img_upload) ? implode(', ', $diamond->multi_img_upload) : '',
            $diamond->created_at->format('Y-m-d H:i:s'),
            $diamond->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lot No',
            'SKU',
            'Material',
            'Cut',
            'Clarity',
            'Color',
            'Shape',
            'Measurement',
            'Weight',
            'Per Ct',
            'Purchase Price',
            'Margin',
            'Listing Price',
            'Shipping Price',
            'Purchase Date',
            'Sold Out Date',
            'Status',
            'Duration Days',
            'Duration Price',
            'Sold Out Price',
            'Profit',
            'Sold Out Month',
            'Barcode Number',
            'Description',
            'Note',
            'Diamond Type',
            'Assigned Admin',
            'Assigned By',
            'Assigned At',
            'Images',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Set chunk size for reading/writing large datasets
     * This helps with memory efficiency for large exports
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
