<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Welcome to NextHire</title>
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
            background-color: #1e3a8a;
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
        .credentials-box {
            background-color: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .credentials-box p {
            margin: 5px 0;
            font-size: 15px;
        }
        .credentials-box strong {
            color: #1e293b;
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
                            <p>Hi {{ $employee->first_name }},</p>
                            <p>Welcome to <strong>NextHire</strong>! Your employee account has been successfully created by the HR Department.</p>
                            <p>To access the NextHire internal systems, you will need to log in using your official credentials.</p>
                            
                            <div class="credentials-box">
                                <p><strong>Email Address:</strong> {{ $employee->email }}</p>
                                <p><strong>Employee ID:</strong> {{ $employee->emp_id }}</p>
                            </div>
                            
                            <p>Please note that you <strong>do not need a password</strong> to log in. You must use your exact <strong>Employee ID</strong> as your secure login credential alongside your email address.</p>
                            <p>We have attached an official PDF credential report to this email for your records. Please keep it safe.</p>
                            <p>Best regards,<br>The NextHire HR Team</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p>&copy; {{ date('Y') }} NextHire. All rights reserved.</p>
                            <p>This is an automated message containing sensitive credential information. Please do not forward this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
