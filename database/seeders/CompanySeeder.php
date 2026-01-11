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
                'name' => 'OM GEMS',
                'email' => 'omgemsindia@gmail.com',
                'phone' => '+91 7698279481',
                'logo' => null,
                'gst_no' => '24FPJPS7743F1ZG',
                'state_code' => '27',
                'ein_cin_no' => null,
                'address' => "10TH FL. OFFICE-1003 ARMIEDA, MINI BAZAR, VARACHHA\nSURAT-395006",
                'country' => 'India',
                'bank_name' => 'Axis Bank Limited',
                'account_no' => '919020077329391',
                'ifsc_code' => 'UTIB0000848',
                'ad_code' => '63608685600009',
                'sort_code' => null,
                'swift_code' => 'AXISINBB047',
                'iban' => null,
                'account_holder_name' => 'OM GEMS',
                'beneficiary_name' => null,
                'aba_routing_number' => null,
                'us_account_no' => null,
                'account_type' => null,
                'beneficiary_address' => null,
                'currency' => 'INR',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'MINIMAL CARBON',
                'email' => 'minimalcarbonllc@gmail.com',
                'phone' => '+91 7096297252',
                'logo' => null,
                'gst_no' => '24NCHPS4406L1Z2',
                'state_code' => '27',
                'ein_cin_no' => null,
                'address' => "10TH FL. OFFICE-1003 ARMIEDA, MINI BAZAR, VARACHHA\nSURAT-395006",
                'country' => 'India',
                'bank_name' => 'Axis Bank Limited',
                'account_no' => '923020007827934',
                'ifsc_code' => 'UTIB0000848',
                'ad_code' => null,
                'sort_code' => null,
                'swift_code' => 'AXISINBB848',
                'iban' => null,
                'account_holder_name' => 'Minimal Carbon',
                'beneficiary_name' => null,
                'aba_routing_number' => null,
                'us_account_no' => null,
                'account_type' => null,
                'beneficiary_address' => null,
                'currency' => 'INR',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'SHIVAGEMS',
                'email' => 'shivagems9026@gmail.com',
                'phone' => '+91 8980845003',
                'logo' => null,
                'gst_no' => '24FUGPS9353P1ZM',
                'state_code' => '27',
                'ein_cin_no' => null,
                'address' => '10 TH FLOOR. OFFICE-1003 ARMIEDA, MINI BAZAR, VARACHHA ROAD, SURAT-395006, GUJARAT',
                'country' => 'India',
                'bank_name' => 'Axis Bank Limited',
                'account_no' => '925020030192041',
                'ifsc_code' => 'UTIB0000848',
                'ad_code' => null,
                'sort_code' => null,
                'swift_code' => 'AXISINBB848',
                'iban' => null,
                'account_holder_name' => 'SHIVA GEMS',
                'beneficiary_name' => null,
                'aba_routing_number' => null,
                'us_account_no' => null,
                'account_type' => null,
                'beneficiary_address' => null,
                'currency' => 'INR',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'SHREEJI JEWELS',
                'email' => 'shreejijewelstudio@gmail.com',
                'phone' => '+91 8980845003',
                'logo' => null,
                'gst_no' => '24MJNPS1170B1Z4',
                'state_code' => '27',
                'ein_cin_no' => null,
                'address' => '10 TH FLOOR. OFFICE-1003 ARMIEDA, MINI BAZAR, VARACHHA ROAD, SURAT-395006, GUJARAT',
                'country' => 'India',
                'bank_name' => 'Axis Bank Limited',
                'account_no' => '923020047779620',
                'ifsc_code' => 'UTIB0000848',
                'ad_code' => null,
                'sort_code' => null,
                'swift_code' => 'AXISINBB848',
                'iban' => null,
                'account_holder_name' => 'SHREEJI JEWELS',
                'beneficiary_name' => null,
                'aba_routing_number' => null,
                'us_account_no' => null,
                'account_type' => null,
                'beneficiary_address' => null,
                'currency' => 'INR',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'MINIMAL CARBON LLC',
                'email' => 'minimalcarbonllc@gmail.com',
                'phone' => '+1 512-555-0100',
                'logo' => null,
                'gst_no' => null,
                'state_code' => null,
                'ein_cin_no' => '84-1234567',
                'address' => '5900 Balcones Drive STE 100, Austin TX 78731, USA',
                'country' => 'USA',
                'bank_name' => 'Chase Bank',
                'account_no' => null,
                'ifsc_code' => null,
                'ad_code' => null,
                'sort_code' => null,
                'swift_code' => 'CHASUS33',
                'iban' => null,
                'account_holder_name' => 'Minimal Carbon LLC',
                'beneficiary_name' => 'Minimal Carbon LLC',
                'aba_routing_number' => '021000021',
                'us_account_no' => '123456789012',
                'account_type' => 'checking',
                'beneficiary_address' => '5900 Balcones Drive STE 100, Austin TX 78731, USA',
                'currency' => 'USD',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'MINIMAL CARBON LTD',
                'email' => 'info@minimalcarbon.co.uk',
                'phone' => '+44 20 7946 0958',
                'logo' => null,
                'gst_no' => null,
                'state_code' => null,
                'ein_cin_no' => '12345678',
                'address' => '71-75 Shelton Street, Covent Garden, London WC2H 9JQ, United Kingdom',
                'country' => 'United Kingdom',
                'bank_name' => 'Barclays Bank UK PLC',
                'account_no' => '12345678',
                'ifsc_code' => null,
                'ad_code' => null,
                'sort_code' => '20-00-00',
                'swift_code' => 'BUKBGB22',
                'iban' => 'GB82 WEST 1234 5698 7654 32',
                'account_holder_name' => 'Minimal Carbon Ltd',
                'beneficiary_name' => 'Minimal Carbon Ltd',
                'aba_routing_number' => null,
                'us_account_no' => null,
                'account_type' => null,
                'beneficiary_address' => '71-75 Shelton Street, Covent Garden, London WC2H 9JQ, UK',
                'currency' => 'GBP',
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