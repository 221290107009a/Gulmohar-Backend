<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background: #cce5ff;
            border-left: 4px solid #004085;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #004085;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        .details-table th, 
        .details-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .details-table th {
            background-color: #f8f9fa;
            color: #1e3c72;
            text-align: left;
            font-weight: 600;
            width: 40%;
        }
        .details-table td {
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            background: #2a5298;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
        }
        .verification-section {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .verification-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }
        .verification-list li {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }
        .verification-list li:before {
            content: "•";
            color: #2a5298;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .timestamp {
            color: #888;
            font-size: 14px;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Sangho Admin" height="40">
            </div>
            <h1>New Seller Registration</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <strong>New Registration Alert:</strong> A new seller has registered on the platform.
            </div>

            <table class="details-table">
                <tr>
                    <th>Shop Name</th>
                    <td>{{ $seller->shop_name }}</td>
                </tr>
                <tr>
                    <th>Owner Name</th>
                    <td>{{ $seller->owner_name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $seller->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $seller->phone }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ ucfirst($seller->category) }}</td>
                </tr>
                <tr>
                    <th>GST Number</th>
                    <td>{{ $seller->gst_number ?: 'Not Provided' }}</td>
                </tr>
                <tr>
                    <th>PAN Number</th>
                    <td>{{ $seller->pan_number ?: 'Not Provided' }}</td>
                </tr>
                <tr>
                    <th>Current Status</th>
                    <td>
                        <span class="status-badge status-{{ $seller->current_status }}">
                            {{ ucfirst(str_replace('_', ' ', $seller->current_status)) }}
                        </span>
                    </td>
                </tr>
            </table>

            <div class="verification-section">
                <h3 style="color: #1e3c72; margin-top: 0;">Verification Checklist:</h3>
                <ul class="verification-list">
                    <li>Verify shop name and business details</li>
                    <li>Check GST number validity</li>
                    <li>Validate PAN number</li>
                    <li>Review business category selection</li>
                    <li>Verify contact information</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="action-button">Review Application</a>
            </div>

            <div class="timestamp">
                <p>Registration received on: {{ now()->format('F j, Y g:i A') }}</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated message from Sangho Admin Panel</p>
            <p>&copy; {{ date('Y') }} Sangho. All rights reserved.</p>
        </div>
    </div>
</body>
</html>