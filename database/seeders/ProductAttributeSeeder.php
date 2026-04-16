<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Страна',
                'slug' => 'country',
                'type' => 'string',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'origin',
                'sort_order' => 10,
            ],
            [
                'name' => 'Регион',
                'slug' => 'region',
                'type' => 'string',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'origin',
                'sort_order' => 20,
            ],
            [
                'name' => 'Высота',
                'slug' => 'altitude',
                'type' => 'number',
                'unit' => 'м',
                'is_visible' => true,
                'display_group' => 'origin',
                'sort_order' => 30,
            ],
            [
                'name' => 'Разновидность',
                'slug' => 'variety',
                'type' => 'string',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'origin',
                'sort_order' => 40,
            ],
            [
                'name' => 'Обработка',
                'slug' => 'processing',
                'type' => 'select',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'profile',
                'sort_order' => 50,
            ],
            [
                'name' => 'Степень обжарки',
                'slug' => 'roast_level',
                'type' => 'select',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'profile',
                'sort_order' => 60,
            ],
            [
                'name' => 'Кислотность',
                'slug' => 'acidity',
                'type' => 'select',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'profile',
                'sort_order' => 70,
            ],
            [
                'name' => 'Тело',
                'slug' => 'body',
                'type' => 'select',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'profile',
                'sort_order' => 80,
            ],
            [
                'name' => 'Дескрипторы вкуса',
                'slug' => 'taste_notes',
                'type' => 'text',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'profile',
                'sort_order' => 90,
            ],
            [
                'name' => 'Рекомендация по завариванию',
                'slug' => 'brew_recommendation',
                'type' => 'text',
                'unit' => null,
                'is_visible' => true,
                'display_group' => 'brewing',
                'sort_order' => 100,
            ],
        ];

        foreach ($attributes as $attribute) {
            ProductAttribute::updateOrCreate(
                ['slug' => $attribute['slug']],
                $attribute
            );
        }
    }
}
