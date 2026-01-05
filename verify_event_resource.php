<?php

use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\EventImage;
use App\Models\User;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure we have data
$user = User::first() ?? User::factory()->create();
Auth::login($user);

$organizer = User::first();
$event = Event::where('organizer_id', $organizer->id)->first();

if (!$event) {
    echo "No event found. Creating one...\n";
    $event = Event::create([
        'title' => 'Test Event',
        'slug' => 'test-event-' . time(),
        'description' => 'Test Description',
        'organizer_id' => $organizer->id,
        'municipality_id' => 1,
        'start_datetime' => now()->addDays(1),
        'end_datetime' => now()->addDays(2),
        'total_seat' => 100,
        'remaining_seat' => 100,
        'status' => 'upcoming',
        'city' => 'Kathmandu',
        'latitude' => 27.7172,
        'longitude' => 85.3240,
    ]);
}

// Add Image
if ($event->images()->count() == 0) {
    EventImage::create([
        'event_id' => $event->id,
        'image' => 'test-image.jpg'
    ]);
}

// Add Feedback
if ($event->feedbacks()->count() == 0) {
    EventFeedback::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'comment' => 'Great event!'
    ]);
}

$service = new EventService();
$loadedEvent = $service->showBySlug($event->slug);

$resource = new EventResource($loadedEvent);
$json = json_encode($resource->resolve(), JSON_PRETTY_PRINT);

echo $json . "\n";
