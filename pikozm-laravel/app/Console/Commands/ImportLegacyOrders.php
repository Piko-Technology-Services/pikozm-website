<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * One-off migration of payments/data/orders.json into the orders table.
 *
 *   php artisan piko:import-orders                     (default path)
 *   php artisan piko:import-orders /path/to/orders.json
 */
class ImportLegacyOrders extends Command
{
    protected $signature = 'piko:import-orders {path? : Path to the legacy orders.json}';

    protected $description = 'Import DPO orders from the old flat-file payments/data/orders.json';

    public function handle(): int
    {
        $path = $this->argument('path') ?? base_path('../payments/data/orders.json');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $legacy = json_decode((string) file_get_contents($path), true);

        if (! is_array($legacy)) {
            $this->error('The file does not contain valid JSON.');

            return self::FAILURE;
        }

        $products = Product::pluck('id', 'name');
        $imported = 0;

        foreach ($legacy as $ref => $row) {
            $companyRef = $row['company_ref'] ?? $ref;

            $order = Order::firstOrNew(['company_ref' => $companyRef]);
            $order->fill([
                'product_id' => $products[$row['product'] ?? ''] ?? null,
                'product_name' => $row['product'] ?? 'Unknown',
                'amount' => (float) ($row['amount'] ?? 0),
                'currency' => 'ZMW',
                'customer_name' => $row['name'] ?? '',
                'customer_email' => $row['email'] ?? '',
                'customer_phone' => $row['phone'] ?? '',
                'notes' => $row['notes'] ?? null,
                'status' => $row['status'] ?? Order::STATUS_PENDING,
                'dpo_token' => $row['token'] ?? null,
                'dpo_trans_ref' => $row['trans_ref'] ?? null,
                'error_message' => $row['error'] ?? null,
                'verify_message' => $row['verify_message'] ?? null,
            ]);

            if (! empty($row['created_at'])) {
                $order->created_at = Carbon::parse($row['created_at']);
            }

            $order->save();
            $imported++;
        }

        $this->info("Imported {$imported} order(s).");

        return self::SUCCESS;
    }
}
