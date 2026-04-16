<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use Illuminate\Database\Seeder;

class ProductAttributeOptionSeeder extends Seeder
{
    public function run(): void
    {
        $optionsMap = [
            'processing' => [
                'Мытая',
                'Натуральная',
                'Хани',
                'Анаэробная',
                'Вет-халл',
            ],
            'roast_level' => [
                'Светлая',
                'Средняя',
                'Средне-темная',
            ],
            'acidity' => [
                'Низкая',
                'Средняя',
                'Высокая',
            ],
            'body' => [
                'Легкое',
                'Среднее',
                'Плотное',
            ],
        ];

        foreach ($optionsMap as $slug => $values) {
            $attribute = ProductAttribute::where('slug', $slug)->first();

            if (! $attribute) {
                continue;
            }

            foreach ($values as $index => $value) {
                ProductAttributeOption::updateOrCreate(
                    [
                        'product_attribute_id' => $attribute->id,
                        'value' => $value,
                    ],
                    [
                        'sort_order' => ($index + 1) * 10,
                    ]
                );
            }
        }
    }
}
