<html>
    <body>
        <div>
            <p>Hi Admin, </p>
        </div>
        <div>
            <p>
                Payment has been received for {{ $event_name }}
            </p>
        </div>
        <div>
            <p>Here are the details:</p>
            <span>Payment ID: {{ $payment_id }} </span><br>
            <span>Organization / School: {{ $organization }} </span><br>
            <span>Contact Person: {{ $contact_person }} </span><br>
            <span>Contact No.: {{ $contact_no }} </span><br>
            <span>Email: {{ $email }} </span><br>
            <span>Payment Date: {{ $payment_date }} </span><br>
        </div>
        <div>
            
        </div>
    </body>
</html>