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
        .status-update-message {
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
        .status-change {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
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
                <img src="https://sanghoapp.phxsolution.com/storage/media/9n8bj83xeddscaFfMiZudZRM4FTG6eDDvhI45Sci.png" alt="Sangho" height="40">
            </div>
            <h1>Status Update</h1>
        </div>
        
        <div class="content">
            <div class="status-update-message">
                <p>Dear {{ $seller->owner_name }},</p>
                <p>Your seller status has been updated!</p>
            </div>

            <div class="status-change">
                <p>Status changed from:</p>
                <p>
                    <span class="status-badge status-{{ $oldStatus }}">
                        {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
                    </span>
                    →
                    <span class="status-badge status-{{ $seller->current_status }}">
                        {{ ucfirst(str_replace('_', ' ', $seller->current_status)) }}
                    </span>
                </p>
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
                    <th>Category</th>
                    <td>{{ ucfirst($seller->category) }}</td>
                </tr>
            </table>

            @if($seller->current_status === 'approved')
                <div class="thank-you">
                    <p>Congratulations! Your seller account has been approved. You can now start selling on Sangho!</p>
                </div>
            @elseif($seller->current_status === 'rejected')
                <div class="thank-you">
                    <p>We regret to inform you that your seller account has been rejected. Please contact support for more information.</p>
                </div>
            @else
                <div class="thank-you">
                    <p>We are reviewing your application and will keep you updated on any changes.</p>
                </div>
            @endif

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