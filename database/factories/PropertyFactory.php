<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = \App\Models\Property::class;

    public function definition(): array
    {
        $cities = ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Mandaluyong', 'San Juan', 'Marikina'];
        $amenities = ['WiFi', 'Air Conditioning', 'Parking', 'Security', 'Laundry', 'Kitchen', 'Gym', 'Pool'];

        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(5),
            'price' => fake()->randomFloat(2, 2000, 15000),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement($cities),
            'state' => 'Metro Manila',
            'zip_code' => fake()->postcode(),
            'country' => 'Philippines',
            'latitude' => fake()->latitude(14.4, 14.7),
            'longitude' => fake()->longitude(120.9, 121.1),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 3),
            'capacity' => fake()->numberBetween(1, 10),
            'amenities' => fake()->randomElements($amenities, rand(2, 5)),
            'property_type' => fake()->randomElement(['boarding_house', 'apartment', 'dormitory', 'other']),
            'status' => 'approved',
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->email(),
        ];
    }
}

