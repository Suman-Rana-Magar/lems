<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $startDate = $this->faker->dateTimeBetween('-1 month', '+6 months');
        $endDate = clone $startDate;
        $endDate->modify('+' . rand(2, 48) . ' hours');

        $totalSeat = $this->faker->numberBetween(10, 500);

        return [
            'title' => $title,
            'description' => $this->faker->paragraphs(3, true),
            'organizer_id' => \App\Models\User::where('role', \App\Enums\RoleEnum::ORGANIZER->value)->inRandomOrder()->first()?->id ?? \App\Models\User::first()?->id,
            'municipality_id' => \App\Models\Municipality::inRandomOrder()->first()?->id,
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'total_seat' => $totalSeat,
            'remaining_seat' => $totalSeat,
            'status' => $this->faker->randomElement(['upcoming', 'ongoing', 'completed', 'cancelled']),
            'view_count' => $this->faker->numberBetween(0, 5000),
            'seat_price' => $this->faker->randomFloat(2, 0, 1000),
            'map_address' => $this->faker->address,
            'map_url' => 'https://maps.google.com/?q=' . $this->faker->latitude . ',' . $this->faker->longitude,
            'city' => $this->faker->city,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'cover_image' => 'https://picsum.photos/seed/' . \Illuminate\Support\Str::random(10) . '/800/600',
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . \Illuminate\Support\Str::random(5),
            'tags' => $this->faker->words(3),
        ];
    }
}
