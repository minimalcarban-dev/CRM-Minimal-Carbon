<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Console\Command;

class SyncClientsFromOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clients:sync-from-orders';

    /**
     * The console command description.
     */
    protected $description = 'Import unique clients from existing orders into the clients table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting client sync from existing orders...');

        $created = 0;
        $skipped = 0;

        // Get unique email/name combinations from orders
        $orders = Order::whereNotNull('client_email')
            ->orWhereNotNull('client_name')
            ->select('client_name', 'client_email', 'client_mobile', 'client_address', 'client_tax_id', 'submitted_by')
            ->distinct()
            ->get();

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $email = $order->client_email;

            // Skip if no email (can't create unique client without email)
            if (empty($email)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Check if client already exists
            $existing = Client::where('email', $email)->first();

            if ($existing) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Create new client
            Client::create([
                'name' => $order->client_name,
                'email' => $order->client_email,
                'address' => $order->client_address,
                'mobile' => $order->client_mobile,
                'tax_id' => $order->client_tax_id,
                'created_by' => $order->submitted_by,
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync complete!");
        $this->info("Created: {$created} clients");
        $this->info("Skipped (already exist or no email): {$skipped}");

        // Now link orders to clients
        $this->info('Linking existing orders to clients...');
        $linked = 0;

        $ordersToLink = Order::whereNull('client_id')
            ->whereNotNull('client_email')
            ->get();

        foreach ($ordersToLink as $order) {
            $client = Client::where('email', $order->client_email)->first();
            if ($client) {
                $order->client_id = $client->id;
                $order->save();
                $linked++;
            }
        }

        $this->info("Linked: {$linked} orders to clients");

        return Command::SUCCESS;
    }
}
