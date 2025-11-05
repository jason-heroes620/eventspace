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
   <div class="email-container">
        <div  class="email-header">
            <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120" class="email-logo">
        </div>                 

        <div class="email-body">
            <p>Hey {{ $name }},</p>
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