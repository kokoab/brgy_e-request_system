<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '1234567890',
        ]);

        // Create regular users
        $users = User::factory(10)->create([
            'role' => 'user',
        ]);

        // Create properties for users
        foreach ($users as $user) {
            $properties = Property::factory(rand(1, 3))->create([
                'user_id' => $user->id,
                'status' => 'approved',
            ]);

            foreach ($properties as $property) {
                // Add images
                PropertyImage::factory(rand(1, 5))->create([
                    'property_id' => $property->id,
                ]);

                // Add reviews from other users
                $reviewers = $users->random(rand(0, 3));
                foreach ($reviewers as $reviewer) {
                    if ($reviewer->id !== $user->id) {
                        Review::factory()->create([
                            'property_id' => $property->id,
                            'user_id' => $reviewer->id,
                        ]);
                    }
                }
            }
        }

        // Create some pending properties
        Property::factory(5)->create([
            'user_id' => $users->random()->id,
            'status' => 'pending',
        ]);
    }
}

