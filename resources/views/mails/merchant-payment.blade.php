<html>

<body>
    <div>
        <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120">
    </div>
    <div>
        <p>Hey {{ $name }},</p>
    </div>
    <p></p>
    <div>
        <p>We are thrilled to confirm your successful booth purchase for {{ $event_name }}.</p>
    </div>
    <p></p>
    <div>
        Thank you for choosing us to be a part of your upcoming experience!
    </div>
    <p></p>
    <div>
        <p>Here are the details of your purchase</p>
        <span>Event Name: {{ $event_name }}</span><br>
        <span>Date: {{ $event_date }}</span><br>
        <span>Time: {{ $event_time }}</span><br>
        <span>Location / Venue: {{ $event_location }}</span><br>
        <span>Selected booth: {{ $booth }}</span><br>
        <span>No. of booths: {{ $booth_qty }}</span><br>
    </div>
    <p></p>
    <div>
        <p>Your payment has been processed successfully. Please keep this confirmation email for reference on the day of the activity.</p>
    </div>
    <p></p>
    <div>
        <p>If you have any queries or require further assistance regarding your booking, feel free to reach out to Heroes Customer Support at 012 7456785 (9am - 5pm). We're here to ensure you have a seamless and enjoyable experience.</p>
    </div>
    <p></p>
    <div>
        <p>Thank you once again for choosing Heroes. We sincerely appreciate your trust in us.</p>
    </div>
</body>

</html>