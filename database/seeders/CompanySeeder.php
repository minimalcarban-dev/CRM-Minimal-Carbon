<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $companies = [
            [
                'name' => 'OM Gems',
                'email' => 'contact@omgems.com',
                'phone' => '+91-9876543210',
                'logo' => null,
                'gst_no' => '27AABCO1234H1ZP',
                'state_code' => '27',
                'ein_cin_no' => 'U74999MH2020PTC123456',
                'address' => 'Office 501, Diamond Tower, BKC, Bandra East, Mumbai - 400051, Maharashtra, India',
                'country' => 'India',
                'bank_name' => 'HDFC Bank Ltd.',
                'account_no' => '50100123456789',
                'ifsc_code' => 'HDFC0001234',
                'ad_code' => '0510123',
                'sort_code' => null,
                'swift_code' => 'HDFCINBBXXX',
                'iban' => null,
                'account_holder_name' => 'OMGems Pvt. Ltd.',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Minimal Carbon',
                'email' => 'info@minimalcarbon.com',
                'phone' => '+91-9123456789',
                'logo' => null,
                'gst_no' => '07AAFCM5678K1ZQ',
                'state_code' => '07',
                'ein_cin_no' => 'AAH-2019',
                'address' => 'Plot No. 45, Sector 18, Noida - 201301, Uttar Pradesh, India',
                'country' => 'India',
                'bank_name' => 'ICICI Bank Ltd.',
                'account_no' => '006705001234',
                'ifsc_code' => 'ICIC0000067',
                'ad_code' => '0670789',
                'sort_code' => null,
                'swift_code' => 'ABORINBBXXX',
                'iban' => null,
                'account_holder_name' => 'Minimal Carbon LLP',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Shiva Gems',
                'email' => 'sales@shivagems.com',
                'phone' => '+91-8765432109',
                'logo' => null,
                'gst_no' => '29AABCS9012L1ZR',
                'state_code' => '29',
                'ein_cin_no' => 'U36999KA2018PTC098765',
                'address' => '123, Jewellers Street, Commercial Road, Chickpet, Bangalore - 560053, Karnataka, India',
                'country' => 'India',
                'bank_name' => 'State Bank of India',
                'account_no' => '38765432109',
                'ifsc_code' => 'SBIN0001234',
                'ad_code' => '1234567',
                'sort_code' => null,
                'swift_code' => 'SBININBBXXX',
                'iban' => null,
                'account_holder_name' => 'Shiva Gems & Jewellery',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Shreeji',
                'email' => 'support@shreeji.com',
                'phone' => '+91-7654321098',
                'logo' => null,
                'gst_no' => '24AADCS3456M1ZS',
                'state_code' => '24',
                'ein_cin_no' => 'U74120GJ2015PTC087654',
                'address' => 'Diamond Bourse, Mahidharpura, Surat - 395003, Gujarat, India',
                'country' => 'India',
                'bank_name' => 'Kotak Mahindra Bank',
                'account_no' => '1234567890123',
                'ifsc_code' => 'KKBK0005678',
                'ad_code' => '5678901',
                'sort_code' => null,
                'swift_code' => 'KKBKINBBXXX',
                'iban' => null,
                'account_holder_name' => 'Shreeji Diamond Corporation',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        foreach ($companies as $company) {
            DB::table('companies')->updateOrInsert(
                ['name' => $company['name']],
                $company
            );
        }
    }
}