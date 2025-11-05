<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Event Ticket</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .ticket-container {
            width: 700px;
            border: 2px dashed #444;
            margin: 20px auto;
            padding: 20px;
            position: relative;
            background: #fff9f0;
            border-radius: 15px;
        }

        .ticket-header {
            text-align: center;
            background: #2c3e50;
            color: #fff;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        .ticket-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .ticket-body {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .ticket-details-left,
        .ticket-details-right {
            width: 48%;
        }

        .ticket-details-left p,
        .ticket-details-right p {
            margin: 5px 0;
            font-size: 14px;
        }

        .ticket-details-right a {
            color: #007bff;
            text-decoration: none;
        }

        .ticket-details-right a:hover {
            text-decoration: underline;
        }

        .qr-code {
            margin-top: 15px;
            text-align: center;
        }

        .qr-code img {
            width: 120px;
            height: 120px;
        }

        .ticket-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h1>{{ $event->title }}</h1>
        </div>

        <div class="ticket-body">
            <!-- Left Column: User & Event Details -->
            <div class="ticket-details-left">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Seats Booked:</strong> {{ $registration->seats_booked }}</p>
                <p><strong>Date:</strong> {{ $event->start_datetime->format('d M Y, h:i A') }} -
                    {{ $event->end_datetime->format('d M Y, h:i A') }}</p>
                <p><strong>Location:</strong>
                    @if ($event->map_url)
                        <a href="{{ $event->map_url }}" target="_blank">{{ $event->map_address ?? 'View on Map' }}</a>
                    @else
                        {{ $event->map_address ?? 'N/A' }}
                    @endif
                </p>
                <p><strong>Ticket ID:</strong> {{ $registration->id }}</p>
                <p><strong>Total Price:</strong>
                    Rs. {{ number_format($event->seat_price * $registration->seats_booked, 2) }}</p>
            </div>

            <!-- Right Column: Organizer + QR -->
            <div class="ticket-details-right">
                <p><strong>Organizer Name:</strong> {{ $event->organizer->name }}</p>
                <p><strong>Phone:</strong> {{ $event->organizer->phone_no }}</p>

                <div class="qr-code">
                    <img src="data:image/png;base64, {!! base64_encode(
                        QrCode::format('png')->size(120)->generate(
                                json_encode([
                                    'registration_id' => $registration->id,
                                    'user_id' => $user->id,
                                    'event_id' => $event->id,
                                ]),
                            ),
                    ) !!}">
                </div>
                <p>Scan for Verification</p>
            </div>
        </div>

        <div class="ticket-footer">
            <p>Present this ticket at the event entrance. QR code must be scannable.</p>
        </div>
    </div>
</body>

</html>
