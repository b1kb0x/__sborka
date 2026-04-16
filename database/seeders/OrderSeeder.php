<?php

namespace Database\Seeders;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\GrindType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '+380500000002',
                'region' => 'Kyiv region',
                'city' => 'Kyiv',
                'address' => 'Customer street 10',
                'password' => bcrypt('password'),
                'role' => UserRole::Customer,
                'status' => UserStatus::Active,
            ]
        );

        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command?->warn('OrderSeeder skipped: no active products found.');
            return;
        }

        $orderStatuses = [
            OrderStatus::New,
            OrderStatus::Paid,
            OrderStatus::Completed,
        ];

        $fulfillmentStatuses = [
            FulfillmentStatus::Accepted,
            FulfillmentStatus::Roasting,
            FulfillmentStatus::HandedToCarrier,
            FulfillmentStatus::Delivered,
        ];

        $grindValues = array_map(
            fn (array $option) => $option['value'],
            GrindType::options()
        );

        for ($i = 1; $i <= 10; $i++) {
            $pickedProducts = $products->shuffle()->take(rand(1, min(3, $products->count())));
            $status = Arr::random($orderStatuses);

            $fulfillmentStatus = match ($status) {
                OrderStatus::New => FulfillmentStatus::Accepted,
                OrderStatus::Paid => Arr::random([
                    FulfillmentStatus::Accepted,
                    FulfillmentStatus::Roasting,
                    FulfillmentStatus::HandedToCarrier,
                ]),
                OrderStatus::Completed => FulfillmentStatus::Delivered,
                default => FulfillmentStatus::Accepted,
            };

            $order = Order::query()->create([
                'user_id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'region' => $customer->region,
                'city' => $customer->city,
                'address' => $customer->address,
                'comment' => 'Seeded order #'.$i,
                'subtotal' => 0,
                'total' => 0,
                'status' => $status,
                'fulfillment_status' => $fulfillmentStatus,
                'carrier_name' => $fulfillmentStatus === FulfillmentStatus::HandedToCarrier || $fulfillmentStatus === FulfillmentStatus::Delivered
                    ? 'Nova Poshta'
                    : null,
                'tracking_number' => $fulfillmentStatus === FulfillmentStatus::HandedToCarrier || $fulfillmentStatus === FulfillmentStatus::Delivered
                    ? 'NP-SEED-'.$i
                    : null,
                'handed_to_carrier_at' => $fulfillmentStatus === FulfillmentStatus::HandedToCarrier || $fulfillmentStatus === FulfillmentStatus::Delivered
                    ? now()->subDays(rand(1, 5))
                    : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);

            $subtotal = 0;

            foreach ($pickedProducts as $product) {
                $qty = rand(1, 3);
                $unitPrice = (int) $product->price;
                $lineTotal = $unitPrice * $qty;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'grind_type' => Arr::random($grindValues),
                ]);

                $subtotal += $lineTotal;
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);
        }
    }
}
