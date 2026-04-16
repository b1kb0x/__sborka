<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = ProductAttribute::query()
            ->with('options')
            ->get()
            ->keyBy('slug');

        $products = [
            [
                'title' => 'Ethiopia Guji',
                'slug' => 'ethiopia-guji',
                'short_description' => 'Bright and floral specialty coffee.',
                'description' => 'Notes of bergamot, jasmine and stone fruits. Freshly roasted after order.',
                'price' => 549,
                'stock' => 20,
                'is_active' => true,
                'attributes' => [
                    'country' => ['value_string' => 'Эфиопия'],
                    'region' => ['value_string' => 'Guji'],
                    'altitude' => ['value_number' => 2100],
                    'variety' => ['value_string' => 'Heirloom'],
                    'processing' => ['option' => 'Мытая'],
                    'roast_level' => ['option' => 'Светлая'],
                    'acidity' => ['option' => 'Высокая'],
                    'body' => ['option' => 'Среднее'],
                    'taste_notes' => ['value_text' => 'Бергамот, жасмин, косточковые фрукты'],
                    'brew_recommendation' => ['value_text' => 'V60, Kalita, AeroPress'],
                ],
            ],
            [
                'title' => 'Colombia Huila',
                'slug' => 'colombia-huila',
                'short_description' => 'Balanced and sweet specialty coffee.',
                'description' => 'Notes of caramel, red apple and cacao. Freshly roasted after order.',
                'price' => 499,
                'stock' => 15,
                'is_active' => true,
                'attributes' => [
                    'country' => ['value_string' => 'Колумбия'],
                    'region' => ['value_string' => 'Huila'],
                    'altitude' => ['value_number' => 1800],
                    'variety' => ['value_string' => 'Caturra, Castillo'],
                    'processing' => ['option' => 'Мытая'],
                    'roast_level' => ['option' => 'Средняя'],
                    'acidity' => ['option' => 'Средняя'],
                    'body' => ['option' => 'Среднее'],
                    'taste_notes' => ['value_text' => 'Карамель, красное яблоко, какао'],
                    'brew_recommendation' => ['value_text' => 'Эспрессо, V60, френч-пресс'],
                ],
            ],
            [
                'title' => 'Kenya AA',
                'slug' => 'kenya-aa',
                'short_description' => 'Juicy and vibrant specialty coffee.',
                'description' => 'Notes of blackcurrant, citrus and berries. Freshly roasted after order.',
                'price' => 599,
                'stock' => 10,
                'is_active' => true,
                'attributes' => [
                    'country' => ['value_string' => 'Кения'],
                    'region' => ['value_string' => 'Nyeri'],
                    'altitude' => ['value_number' => 1900],
                    'variety' => ['value_string' => 'SL28, SL34'],
                    'processing' => ['option' => 'Мытая'],
                    'roast_level' => ['option' => 'Светлая'],
                    'acidity' => ['option' => 'Высокая'],
                    'body' => ['option' => 'Плотное'],
                    'taste_notes' => ['value_text' => 'Черная смородина, цитрус, ягоды'],
                    'brew_recommendation' => ['value_text' => 'V60, AeroPress, пуровер'],
                ],
            ],
        ];

        foreach ($products as $item) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'is_active' => $item['is_active'],
                ]
            );

            $product->attributeValues()->delete();

            foreach ($item['attributes'] as $slug => $valueData) {
                $attribute = $attributes->get($slug);

                if (! $attribute) {
                    continue;
                }

                $optionId = null;

                if (isset($valueData['option'])) {
                    $option = $attribute->options->firstWhere('value', $valueData['option']);
                    $optionId = $option?->id;
                }

                ProductAttributeValue::query()->create([
                    'product_id' => $product->id,
                    'product_attribute_id' => $attribute->id,
                    'product_attribute_option_id' => $optionId,
                    'value_string' => $valueData['value_string'] ?? null,
                    'value_text' => $valueData['value_text'] ?? null,
                    'value_number' => $valueData['value_number'] ?? null,
                    'value_boolean' => $valueData['value_boolean'] ?? null,
                ]);
            }
        }
    }
}
