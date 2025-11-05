<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Email' }}</title>
    <style>
        /* Base Styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f6f9fc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
            text-align: center;
        }

        .email-logo {
            color: white;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }

        .email-body {
            padding: 40px;
            text-align: left;
        }

        .email-content {
            font-size: 16px;
            color: #555;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 40px;
            text-align: left;
            border-top: 1px solid #e9ecef;
        }

        .footer-text {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .footer-links {
            margin-top: 15px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }

        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 30px 0;
        }

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            
            .email-footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div  class="email-container">
       
        <div  class="email-header">
            <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120" class="email-logo">
        </div>                 

        <div class="email-body">
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
        </div>

        <div class="email-footer">
            <span  class="footer-text">Sincerely,</span><br>
            <span  class="footer-text">Heroes Event Team</span>
            <p></p>
            <span  class="footer-text">Contact No.: </span><br>
            <span class="footer-text">012 7456 785</span>
            <p></p>
            <span class="footer-text">Address:</span><br>
            <span class="footer-text">Suite 9.01, Menara Summit</span><br>
            <span class="footer-text">Persiaran Kewajipan, USJ 1,</span><br>
            <span class="footer-text">UEP, 47600 Subang Jaya,</span><br>
            <span class="footer-text">Selangor</span><br>
        </div>
    </div>
</body>

</html>