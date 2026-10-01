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
            background: #e3f2fd;
            border-left: 4px solid #1e88e5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        .status-arrow {
            font-size: 24px;
            color: #1e3c72;
            margin: 0 10px;
            vertical-align: middle;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            background: #2a5298;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            transition: background 0.3s;
        }
        .action-button:hover {
            background: #1e3c72;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        .timestamp {
            color: #888;
            font-size: 14px;
            text-align: right;
            margin-top: 10px;
        }
        .status-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .status-message.approved {
            background-color: #f0fff4;
            border: 1px solid #c6f6d5;
            color: #155724;
        }
        .status-message.rejected {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            color: #721c24;
        }
        .status-message.in-review {
            background-color: #ebf8ff;
            border: 1px solid #bee3f8;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="https://sanghoapp.phxsolution.com/storage/media/9n8bj83xeddscaFfMiZudZRM4FTG6eDDvhI45Sci.png" alt="Sangho Admin" height="40">
            </div>
            <h1>Seller Status Update Alert</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <strong>Status Change Notification:</strong> The following seller's status has been updated in the system.
            </div>

            <div class="status-change">
                <h3 style="color: #1e3c72; margin-top: 0;">Status Change Summary</h3>
                <p>
                    <span class="status-badge status-{{ $oldStatus }}">
                        {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
                    </span>
                    <span class="status-arrow">→</span>
                    <span class="status-badge status-{{ $seller->current_status }}">
                        {{ ucfirst(str_replace('_', ' ', $seller->current_status)) }}
                    </span>
                </p>
            </div>

            @if($seller->current_status === 'approved')
                <div class="status-message approved">
                    <strong>Seller Approved:</strong> The seller can now start selling on the platform.
                </div>
            @elseif($seller->current_status === 'rejected')
                <div class="status-message rejected">
                    <strong>Seller Rejected:</strong> The seller has been notified of the rejection.
                </div>
            @elseif($seller->current_status === 'in_review')
                <div class="status-message in-review">
                    <strong>Under Review:</strong> The seller's application is being reviewed.
                </div>
            @endif

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
            </table>

            <div class="action-buttons">
                <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="action-button">View Seller Details</a>
                <a href="{{ route('admin.sellers.index') }}" class="action-button">Manage Sellers</a>
            </div>

            <div class="timestamp">
                <p>Status updated on: {{ now()->format('F j, Y g:i A') }}</p>
                <p>Previous status: {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated message from Sangho Admin Panel</p>
            <p>&copy; {{ date('Y') }} Sangho. All rights reserved.</p>
        </div>
    </div>
</body>
</html>