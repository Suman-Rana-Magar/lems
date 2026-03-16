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
        $eventTypes = [
            'Tech' => ['Kathmandu Code Camp', 'AI & Future Workshop', 'React Native Meetup', 'Cyber Security Summit', 'Web Dev Hackathon', 'Cloud Computing Expo', 'Blockchain Meetup Nepal'],
            'Music' => ['Himalayan Jazz Night', 'Rock the Valley', 'Pop Fusion Festival', 'Classic Evening', 'Open Mic Night', 'Electronic Dance Fest', 'Sufi Soul Night'],
            'Food' => ['Newari Food Festival', 'Momo Mania', 'Street Food Carnival', 'Taste of Kathmandu', 'Bakery Expo', 'Organic Harvest Fair', 'Wine & Cheese Tasting'],
            'Education' => ['Study Abroad Expo', 'Career Counseling Seminar', 'Digital Marketing Masterclass', 'IELTS Workshop', 'Photography Basics', 'Public Speaking Workshop', 'Art and Mindfulness'],
            'Business' => ['Entrepreneurs Meet', 'Startup Pitch Day', 'Banking & Finance Forum', 'E-commerce Conclave', 'Leadership Workshop', 'Investment Summit Nepal', 'Corporate Excellence Awards'],
            'Sports' => ['Valley Futsal Cup', 'Cycling Marathon', 'Yoga Retreat', 'Chess Tournament', 'Cricket League', 'Badminton Open', 'Mountain Bike Challenge'],
        ];

        $category = array_rand($eventTypes);
        $title = $eventTypes[$category][array_rand($eventTypes[$category])];
        
        if (rand(0, 1)) {
            $title .= " " . date('2025');
        }

        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $endDate = clone $startDate;
        $endDate->modify('+' . rand(2, 72) . ' hours');

        $totalSeat = $this->faker->numberBetween(50, 2000);

        // Fetch a real municipality to ensure data consistency
        $municipality = \App\Models\Municipality::with('district')->inRandomOrder()->first();

        // Nepal Bounds Refined: Lat 26.3 to 30.5, Long 80.1 to 88.2
        $latitude = $this->faker->latitude(26.3, 30.5);
        $longitude = $this->faker->longitude(80.1, 88.2);

        return [
            'title' => $title,
            'description' => "Join us for the **{$title}**, a premier event happening in " . ($municipality ? $municipality->name : 'Nepal') . ". 
                This event aims to bring together enthusiasts, professionals, and the general public for an unforgettable experience. 
                Participants will have the opportunity to engage in interactive sessions, network with industry leaders, and explore the latest trends in {$category}. 
                " . $this->faker->paragraph(5),
            'organizer_id' => \App\Models\User::where('role', \App\Enums\RoleEnum::ORGANIZER->value)->inRandomOrder()->first()?->id ?? \App\Models\User::first()?->id,
            'municipality_id' => $municipality?->id,
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'total_seat' => $totalSeat,
            'remaining_seat' => $totalSeat,
            'status' => 'upcoming',
            'view_count' => $this->faker->numberBetween(500, 50000),
            'seat_price' => $this->faker->randomElement([0, 200, 500, 1000, 1500, 2500, 5000]),
            'map_address' => $this->faker->streetName . ', ' . ($municipality ? $municipality->name : 'Kathmandu') . ', ' . ($municipality?->district?->name ?? 'Nepal'),
            'map_url' => "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}",
            'city' => $municipality ? $municipality->name : 'Kathmandu',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'cover_image' => 'https://picsum.photos/seed/' . \Illuminate\Support\Str::random(10) . '/1200/800',
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . \Illuminate\Support\Str::random(5),
            'tags' => [$category, 'Nepal', '2025', 'Event'],
        ];
    }
}
