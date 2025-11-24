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

        .booth-content {
            margin: auto;
            width: 80%;
            justify-content: 'center';
            border: 1px;
            border-color: '#000';
        }

        .payment-content {
            margin: auto;
            width: 90%;
            justify-content: 'center';
            border: 1px;
            border-color: '#000';
        }

        .email-address {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .table {
            border-spacing: 6px 5px;
        }

        .table_amount {
            text-align: right;
        }

        .tr_total {
            border-top: 2px solid black;
            border-bottom: 2px solid black;
        }

        th {
            background-color: #dce0e6;
          
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
            
            <div class="booth-content">
                <table class='table'>
                    <tr>
                        <td><b>Event Dates:</b></td>
                        <td>{{ $event_date }}</td>
                    </tr>
                    <tr>
                        <td><b>Booth: </b></td>
                        <td> {{ $booth_type }}</td>
                    </tr>
                    <tr>
                        <td><b>No. of Booth:</b></td>
                        <td>{{ $booth_qty }}</td>
                    </tr>
                </table>

            </div>
            <div>
                <p> To finalise your participation and secure your booth, please complete the full payment of <b>RM {{ $payment }}</b> within 7 days of receiving this email.</p>
            </div>
            
            <div  class="booth-content">
                @if( $deposit)
                    <table class='table'>
                        <tr>
                            <th style="padding: 0 10px">Description</th>
                            <th class='table_amount' style="padding: 0 10px">Amount (RM)</th>
                        </tr>
                        <tr>
                            <td>Deposit</td>
                           
                            <td class='table_amount'>{{ $deposit_amount }}</td>
                        </tr>
                       
                        <tr>
                            <td>Balance Payment</td>
                      
                            <td class='table_amount'>{{ $subTotal }}</td>
                        </tr>
                        @if($discount)
                            <tr>
                                <td>Discount</td>
                                <td class='table_amount'>{{ $discount_value }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class='tr_total'>Total Payment</td>
                       
                            <td class='table_amount tr_total'>{{ $payment }}</td>
                        </tr>
                    </table>

                @endif
            </div>
            <div>
                <p></p>
                <p>You can make payment through Online Banking:</p>
                <table class='payment-content'>
                    <tr>
                        <td><b>Bank: CIMB</b></td>
                    </tr>
                    <tr>
                        <td><b>Accessible Experiences Sdn Bhd</b></td>
                    </tr>
                    <tr>
                        <td><b>Company Registration No.: 202301002699 (1496618­A)</b></td>
                    </tr>
                    <tr>
                        <td><b>Account Number: <b>86 0546 3742</b></td>
                    </tr>
                </table>
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
           <div class="email-address">
                <div>
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
</body>

</html>