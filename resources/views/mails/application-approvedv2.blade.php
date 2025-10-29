<html>

<body className="bg-black">
    <div>
        <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120">
    </div>
    <div>
        <p>Hi {{ $contact_person }},</p>
    </div>
    <p></p>
    <div>
        <p> We're thrilled to confirm your participation as a vendor at the upcoming <b>"{{ $event_name }}"</b> event at the {{ $venue }} of {{ $location }}!</p>
    </div>
    <p></p>
    <div>
        <p><b>Event Dates: {{ $event_date }}</b></p>
    </div>
    <p></p>
    <div>
        <p>Secure Your Space:</p>
    </div>
    <p></p>
    <div>
        <p> To finalise your participation, please make a full payment for the retail display fee by <b>[{{ date('F d, Y', strtotime($due_date)) }}]</b> with the total of <b>RM {{ $payment }}</b>. You can make payment through:</p>
    </div>
    <p></p>
    <div>
        <p>Online Banking:</p>
        <h4>
        <span>Accessible Experiences Sdn Bhd</span><br>
        <span>Company Registration No.: 202301002699 (1496618­A) </span><br>
        <span>Account Number: <b>86 0546 3742  (CIMB)</b></span><br>
        </h4>
    </div>
    <p></p>
    <div>
        <p>Once you have made payment, please upload your payment receipt by clicking this <a href={{ $upload_reference_link }} target="_blank">Link</a>
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
        <p></p>
        <span>Sincerely,</span><br>
        <span>Heroes Event Team</span>
        <p></p>
        <span>Contact No.: </span><br>
        <span>012 7456 785</span>
        <p></p>
        <span>Address:</span><br>
        <span>Suite 9.01, Menara Summit</span><br>
        <span>Persiaran Kewajipan, USJ 1,</span><br>
        <span>UEP, 47600 Subang Jaya,</span><br>
        <span>Selangor</span><br>
    </div>
</body>

</html>