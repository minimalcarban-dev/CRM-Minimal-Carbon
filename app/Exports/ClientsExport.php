<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Client::withCount('orders')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Mobile',
            'Address',
            'Tax ID',
            'Total Orders',
            'Created At',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,
            $client->name,
            $client->email,
            $client->mobile,
            $client->address,
            $client->tax_id,
            $client->orders_count,
            $client->created_at->format('Y-m-d H:i'),
        ];
    }
}
