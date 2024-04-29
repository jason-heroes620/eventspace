<html>

<body>
    <div>
        <p>Hi Admin,</p>
    </div>
    <div>
        <p>A new application has been received for the event '{{ $event_name }}'.</p>
        <span>Here are the details:</span><br><br>
        <span>Shop/Brand Name/Educational Institute Name: {{ $organization }}</span><br>
        <span>Contact Person: {{ $contact_person }}</span><br>
        <span>Contact No: {{ $contact_no }}</span><br>
        <span>Email: {{ $email }}</span><br>
        <span>Received: {{ date('d/m/Y H:i A', strtotime($created)) }}</span>
    </div>
</body>

</html>