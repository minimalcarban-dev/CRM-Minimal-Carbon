<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            // Some with same names but different details
            ['name' => 'Rahul Sharma', 'email' => 'rahul.sharma@gmail.com', 'mobile' => '+91 98765 43210', 'address' => '123 MG Road, Bangalore 560001', 'tax_id' => 'GSTIN29AABCU1234A'],
            ['name' => 'Rahul Sharma', 'email' => 'rahul.s.business@outlook.com', 'mobile' => '+91 99887 66554', 'address' => '45 Park Street, Kolkata 700016', 'tax_id' => 'GSTIN19AABCU5678B'],
            ['name' => 'Priya Patel', 'email' => 'priya.patel@yahoo.com', 'mobile' => '+91 87654 32109', 'address' => '789 SG Highway, Ahmedabad 380054', 'tax_id' => 'GSTIN24AABCU9012C'],
            ['name' => 'Priya Patel', 'email' => 'priyapatel.jewelry@gmail.com', 'mobile' => '+91 77665 54433', 'address' => '12 Marine Drive, Mumbai 400020', 'tax_id' => null],
            ['name' => 'Amit Kumar', 'email' => 'amit.kumar@hotmail.com', 'mobile' => '+91 90909 08080', 'address' => '567 Connaught Place, New Delhi 110001', 'tax_id' => 'GSTIN07AABCU3456D'],
            ['name' => 'Amit Kumar', 'email' => 'amitkumar.diamonds@gmail.com', 'mobile' => '+91 88776 65544', 'address' => '23 Civil Lines, Jaipur 302006', 'tax_id' => 'GSTIN08AABCU7890E'],

            // Unique realistic clients
            ['name' => 'Anjali Mehta', 'email' => 'anjali.mehta@gmail.com', 'mobile' => '+91 98123 45678', 'address' => '45 Banjara Hills, Hyderabad 500034', 'tax_id' => 'GSTIN36AABCU1122F'],
            ['name' => 'Vikram Singh', 'email' => 'vikram.singh.gems@outlook.com', 'mobile' => '+91 99001 23456', 'address' => '78 Sector 17, Chandigarh 160017', 'tax_id' => 'GSTIN03AABCU3344G'],
            ['name' => 'Sneha Reddy', 'email' => 'sneha.reddy@gmail.com', 'mobile' => '+91 87009 12345', 'address' => '156 Jubilee Hills, Hyderabad 500033', 'tax_id' => null],
            ['name' => 'Rajesh Verma', 'email' => 'rajesh.verma@yahoo.com', 'mobile' => '+91 98987 65432', 'address' => '234 GTB Nagar, Lucknow 226001', 'tax_id' => 'GSTIN09AABCU5566H'],
            ['name' => 'Neha Gupta', 'email' => 'neha.gupta.jewels@gmail.com', 'mobile' => '+91 77889 90011', 'address' => '89 Camac Street, Kolkata 700017', 'tax_id' => 'GSTIN19AABCU7788I'],
            ['name' => 'Arjun Nair', 'email' => 'arjun.nair@hotmail.com', 'mobile' => '+91 94567 89012', 'address' => '12 MG Road, Kochi 682011', 'tax_id' => 'GSTIN32AABCU9900J'],
            ['name' => 'Kavitha Iyer', 'email' => 'kavitha.iyer@gmail.com', 'mobile' => '+91 88990 01122', 'address' => '45 Anna Nagar, Chennai 600040', 'tax_id' => null],
            ['name' => 'Suresh Menon', 'email' => 'suresh.menon.trade@outlook.com', 'mobile' => '+91 97654 32198', 'address' => '78 Indiranagar, Bangalore 560038', 'tax_id' => 'GSTIN29AABCU1234K'],
            ['name' => 'Deepika Joshi', 'email' => 'deepika.joshi@yahoo.com', 'mobile' => '+91 86543 21987', 'address' => '23 Aundh, Pune 411007', 'tax_id' => 'GSTIN27AABCU5678L'],
            ['name' => 'Manish Agarwal', 'email' => 'manish.agarwal@gmail.com', 'mobile' => '+91 95432 10987', 'address' => '567 Hazratganj, Lucknow 226001', 'tax_id' => 'GSTIN09AABCU9012M'],
            ['name' => 'Pooja Saxena', 'email' => 'pooja.saxena.gems@gmail.com', 'mobile' => '+91 84321 09876', 'address' => '12 Vaishali Nagar, Jaipur 302021', 'tax_id' => null],
            ['name' => 'Karthik Rao', 'email' => 'karthik.rao@outlook.com', 'mobile' => '+91 93210 98765', 'address' => '45 Koramangala, Bangalore 560034', 'tax_id' => 'GSTIN29AABCU3456N'],
            ['name' => 'Meera Kapoor', 'email' => 'meera.kapoor@hotmail.com', 'mobile' => '+91 82109 87654', 'address' => '789 Powai, Mumbai 400076', 'tax_id' => 'GSTIN27AABCU7890O'],
            ['name' => 'Sanjay Kulkarni', 'email' => 'sanjay.kulkarni@gmail.com', 'mobile' => '+91 91098 76543', 'address' => '234 FC Road, Pune 411004', 'tax_id' => null],
            ['name' => 'Lakshmi Narayan', 'email' => 'lakshmi.narayan@yahoo.com', 'mobile' => '+91 80987 65432', 'address' => '56 T Nagar, Chennai 600017', 'tax_id' => 'GSTIN33AABCU1122P'],
            ['name' => 'Rohit Bhatt', 'email' => 'rohit.bhatt.diamonds@gmail.com', 'mobile' => '+91 99876 54321', 'address' => '123 CG Road, Ahmedabad 380009', 'tax_id' => 'GSTIN24AABCU3344Q'],
            ['name' => 'Sunita Desai', 'email' => 'sunita.desai@outlook.com', 'mobile' => '+91 88765 43210', 'address' => '45 Model Town, Jalandhar 144001', 'tax_id' => null],
            ['name' => 'Anil Chopra', 'email' => 'anil.chopra@hotmail.com', 'mobile' => '+91 77654 32109', 'address' => '78 Sector 35, Noida 201301', 'tax_id' => 'GSTIN09AABCU5566R'],
            ['name' => 'Rekha Malhotra', 'email' => 'rekha.malhotra@gmail.com', 'mobile' => '+91 66543 21098', 'address' => '234 DLF Phase 2, Gurgaon 122002', 'tax_id' => 'GSTIN06AABCU7788S'],
            ['name' => 'Vijay Shetty', 'email' => 'vijay.shetty.exports@yahoo.com', 'mobile' => '+91 55432 10987', 'address' => '12 Linking Road, Mumbai 400050', 'tax_id' => 'GSTIN27AABCU9900T'],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }

        $this->command->info('Created ' . count($clients) . ' test clients!');
    }
}
