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
            max-width: 650px;
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

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            
            .email-footer {
                padding: 20px;
            }
        }
        .email-address {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div  class="email-header">
            <img src="{{ $message->embed(public_path('/img/heroes-logo.png')) }}" alt="" width="120" class="email-logo">
        </div>                 
        <div class="email-body">
            <div>
                <p>Hi {{ $contact_person }},</p>
            </div>
            <p></p>
            <div>
                <p>We've processed your RM {{ $deposit }} deposit refund to your {{ $bank }} account ({{ $account_no }}).
                </p>
            </div>
            <p></p>
            <div>
                <p>Please check your account and confirm receipt by replying to this email or whatsApp us.</p>
            </div>
            <div>
                <div class="email-footer">
                    <span  class="footer-text">Sincerely,</span><br>
                    <span  class="footer-text">Heroes Event Team</span>
                    <p></p>
                   <div class="email-address">
                        <div >
                            <span  class="footer-text">Contact No.: </span><br>
                            <span class="footer-text">012 7456 785</span>
                        </div>
        
                        <div>
                            <span class="footer-text">Address:</span><br>
                            <span class="footer-text">Suite 9.01, Menara Summit</span><br>
                            <span class="footer-text">Persiaran Kewajipan, USJ 1,</span><br>
                            <span class="footer-text">UEP, 47600 Subang Jaya,</span><br>
                            <span class="footer-text">Selangor</span><br>
                        </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>