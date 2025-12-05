<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyImageFactory extends Factory
{
    protected $model = \App\Models\PropertyImage::class;

    public function definition(): array
    {
        return [
            'property_id' => \App\Models\Property::factory(),
            'image_path' => 'properties/placeholder.jpg',
            'is_primary' => false,
            'order' => 0,
        ];
    }
}

