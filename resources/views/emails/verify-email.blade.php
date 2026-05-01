<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Verify Your NextHire Account</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            color: #333333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f7fa;
            padding: 40px 0;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .email-header {
            background-color: #1e3a8a; /* NextHire Blue */
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #4b5563;
        }
        .button-wrapper {
            text-align: center;
            margin: 35px 0;
        }
        .verify-button {
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .verify-button:hover {
            background-color: #1d4ed8;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .email-footer p {
            color: #64748b;
            font-size: 13px;
            margin: 5px 0;
        }
        .trouble-link {
            font-size: 13px;
            color: #64748b;
            word-break: break-all;
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f5f9;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <h1>NextHire</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="email-body">
                            <p>Hi {{ $name }},</p>
                            <p>Welcome to <strong>NextHire</strong>! We are thrilled to have you onboard.</p>
                            <p>To get started and unlock all features of your candidate profile, please verify your email address by clicking the button below:</p>
                            
                            <div class="button-wrapper">
                                <a href="{{ $url }}" class="verify-button">Verify Email Address</a>
                            </div>
                            
                            <p>If you did not create an account, no further action is required.</p>
                            <p>Best regards,<br>The NextHire Team</p>

                            <div class="trouble-link">
                                If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
                                <br><br>
                                <a href="{{ $url }}" style="color: #2563eb;">{{ $url }}</a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p>&copy; {{ date('Y') }} NextHire. All rights reserved.</p>
                            <p>This is an automated message. Please do not reply directly to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
