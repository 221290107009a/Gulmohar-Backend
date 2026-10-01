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
            background: linear-gradient(135deg, #4158D0 0%, #C850C0 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            color: #4158D0;
            font-size: 18px;
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
            color: #4158D0;
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
        .status-in-review {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .thank-you {
            margin-top: 30px;
            text-align: center;
            color: #666;
        }
        .contact-support {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
        .logo {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <!-- Add your logo here -->
                <img src="https://sanghoapp.phxsolution.com/storage/media/9n8bj83xeddscaFfMiZudZRM4FTG6eDDvhI45Sci.png" alt="Sangho" height="40">
            </div>
            <h1>Welcome to Sangho!</h1>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <p>Dear {{ $seller->owner_name }},</p>
                <p>Thank you for registering your business with Sangho!</p>
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
                    <th>Status</th>
                    <td>
                        <span class="status-badge status-{{ $seller->current_status }}">
                            {{ ucfirst(str_replace('_', ' ', $seller->current_status)) }}
                        </span>
                    </td>
                </tr>
            </table>

            <div class="thank-you">
                <p>Our team will review your application and get back to you soon!</p>
                <p>Thank you for choosing Sangho for your business.</p>
            </div>

            <div class="contact-support">
                <p>If you have any questions, please contact our support team at support@sangho.com</p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Sangho. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>