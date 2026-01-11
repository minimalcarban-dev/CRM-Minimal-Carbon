<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Party;
use App\Models\Invoice;

class PartySeeder extends Seeder
{
    public function run()
    {
        // Create or update OM GEMS party
        $party = Party::updateOrCreate(
            ['name' => 'OM GEMS'],
            [
                'address' => "10TH FLOOR, 1003, ARMIEDA, MINI BAZAR, VARACHHA, SURAT 395006, GUJARAT, INDIA",
                'gst_no' => '24FJPS7743F1ZG',
                'pan_no' => 'FPJPS7743F',
                'state' => 'Gujarat',
                'state_code' => '24',
                'email' => 'omgemsindia@gmail.com',
                'phone' => '+91 7698279481',
                'country' => 'India',
                'is_foreign' => false,
            ]
        );

        // Attach to invoices that have no billed_to_id or shipped_to_id
        Invoice::whereNull('billed_to_id')->update(['billed_to_id' => $party->id]);
        Invoice::whereNull('shipped_to_id')->update(['shipped_to_id' => $party->id]);
    }
}
