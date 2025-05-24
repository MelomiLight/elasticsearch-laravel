<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array {
        $faker = fake('ru');

        return [
            'title' => $faker->sentence(),
            'content' => $faker->paragraph(),
        ];
    }
}
