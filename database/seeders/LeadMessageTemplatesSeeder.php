<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class LeadMessageTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Greeting & Introduction',
                'content' => "Hello {name}, thank you for reaching out to us! How can we help you today with your jewelry requirements?",
                'category' => 'greeting',
            ],
            [
                'name' => 'Price Inquiry',
                'content' => "Hi {name}, thank you for your interest. The price for this piece depends on the diamond specifications. Would you like us to share a detailed quote based on your preferred clarity and cut?",
                'category' => 'sales',
            ],
            [
                'name' => 'Appointment Request',
                'content' => "Hi {name}, we would love to show you our collection in person. Would you like to schedule an appointment at our showroom this week?",
                'category' => 'appointment',
            ],
            [
                'name' => 'Customization Inquiry',
                'content' => "Hello {name}, yes we do specialize in custom jewelry! Could you please share some references or sketches of what you have in mind?",
                'category' => 'customization',
            ],
            [
                'name' => 'Follow Up',
                'content' => "Hi {name}, just following up on our previous conversation. Do you have any further questions about the designs we discussed?",
                'category' => 'follow_up',
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(
                ['name' => $template['name']],
                [
                    'content' => $template['content'],
                    'category' => $template['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
