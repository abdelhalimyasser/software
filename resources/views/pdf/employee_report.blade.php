<!DOCTYPE html>
<html>
<head>
    <title>Employee Registration Report</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #0056b3; margin: 0; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table th, .details-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .details-table th { background-color: #f8f9fa; width: 30%; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to NextHire</h1>
        <p>Employee Official Credential Report</p>
    </div>

    <p>Dear {{ $employee->first_name }} {{ $employee->last_name }},</p>
    <p>Your employee account has been successfully created by the HR Department. Below are your official credentials to access the NextHire internal systems.</p>

    <table class="details-table">
        <tr>
            <th>Full Name</th>
            <td>{{ $employee->name }}</td>
        </tr>
        <tr>
            <th>Email Address</th>
            <td>{{ $employee->email }}</td>
        </tr>
        <tr>
            <th>Employee ID (Login ID)</th>
            <td><strong>{{ $employee->emp_id }}</strong></td>
        </tr>
        <tr>
            <th>System Role</th>
            <td>{{ $employee->role }}</td>
        </tr>
        <tr>
            <th>Registration Date</th>
            <td>{{ $employee->created_at->format('F d, Y') }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px; color: #d9534f; font-weight: bold;">
        Please keep this document secure. You must use your Email Address and your Employee ID (instead of a password) to log into the NextHire portal.
    </p>

    <div class="footer">
        <p>&copy; {{ date('Y') }} NextHire Inc. All rights reserved.</p>
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
