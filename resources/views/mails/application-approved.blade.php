<html>

<body>
    <div>
        <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120">
    </div>
    <div>
        <p>Hi {{ $contact_person }},</p>
    </div>
    <p></p>
    <div>
        <p> We're thrilled to confirm your participation as a vendor at the upcoming "{{ $event_name }}" event at the {{ $venue }} of {{ $location }}!</p>
    </div>
    <p></p>
    <div>
        <p>Event Dates: {{ $event_date }}</p>
    </div>
    <p></p>
    <div>
        <p>Secure Your Space:</p>
    </div>
    <p></p>
    <div>
        <p> To finalise your participation, please make a full payment for the retail display fee by [{{ date('F d, Y'), strtotime($due_date) }}]. You can submit payment through the following:</p>
    </div>
    <p></p>
    <div>
        <p>Online Payment Gateway: {{ $payment_link }}</p>
    </div>
    <p></p>
    <div>
        <p>Next Steps:</p>
    </div>
    <p></p>
    <div>
        <p> Once your payment is received, we'll send you additional information regarding:</p>
    </div>
    <p></p>
    <div>
        <p> Vendor Handbook: This comprehensive guide outlines logistical details, set-up procedures, event expectations, and emergency protocols.</p>
    </div>
    <p></p>
    <div>
        <span>Terms & Conditions:</span><br>
        <span> Please take a moment to review the {{ $event_name }} Terms & Conditions. These terms outline the rights and responsibilities of both vendors and the Organiser.</span>
    </div>
    <p></p>
    <div>
        <p>We look forward to a successful event with you!</p>
    </div>
    <p></p>
    <p></p>
    <div>
        <span>Sincerely,</span><br>
        <span>Heroes Event Team</span>
    </div>
</body>

</html>