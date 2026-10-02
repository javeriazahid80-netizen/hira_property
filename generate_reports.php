<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != 'manager'){
    header("Location: dashboard.php");
    exit();
}

// Fetch Stats
$total_properties = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM properties"));

$available_properties = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM properties WHERE status='available'"));

$occupied_properties = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM properties WHERE status='occupied'"));

$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM rental_requests"));

$approved_bookings = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM rental_requests WHERE status='approved'"));

$pending_bookings = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM rental_requests WHERE status='pending'"));

$rejected_bookings = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM rental_requests WHERE status='rejected'"));

$total_payments = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT SUM(amount) as total FROM payments"));

$total_maintenance = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM maintenance"));

$pending_maintenance = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM maintenance WHERE status='pending'"));

$completed_maintenance = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM maintenance WHERE status='completed'"));

// Fetch Recent Bookings
$recent_bookings = mysqli_query($conn, 
    "SELECT r.*, p.title, p.location, p.price,
     u.first_name, u.last_name
     FROM rental_requests r
     JOIN properties p ON r.property_id = p.id
     JOIN users u ON r.tenant_id = u.id
     ORDER BY r.created_at DESC LIMIT 5");

// Fetch Recent Payments
$recent_payments = mysqli_query($conn,
    "SELECT pay.*, p.title,
     u.first_name, u.last_name
     FROM payments pay
     JOIN properties p ON pay.property_id = p.id
     JOIN users u ON pay.tenant_id = u.id
     ORDER BY pay.payment_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Report - Hira Rentals</title>
    <style>
        body{
            font-family: Arial;
            background: #f5f5f5;
            margin: 0;
        }
        .navbar{
            background: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        .navbar h2{
            color: #E8622A;
            margin: 0;
        }
        .container{
            padding: 30px;
        }
        .stats-grid{
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stat-card h3{
            color: #E8622A;
            font-size: 28px;
            margin: 0;
        }
        .stat-card p{
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .section{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .section h3{
            color: #E8622A;
            margin-top: 0;
            border-bottom: 2px solid #E8622A;
            padding-bottom: 10px;
        }
        table{
            width: 100%;
            border-collapse: collapse;
        }
        th{
            background: #E8622A;
            color: #fff;
            padding: 10px;
            text-align: left;
        }
        td{
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .status-pending{
            background: #fff3cd;
            color: #856404;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-approved{
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-rejected{
            background: #f8d7da;
            color: #721c24;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .print-btn{
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn-back{
            background: #34495e;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-right: 10px;
        }
        .logout{
            background: #E8622A;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        @media print{
            .navbar, .print-btn, .btn-back, .logout{
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Hira Rentals</h2>
        <div>
            <a href="dashboard.php" class="btn-back">
                ← Back to Dashboard
            </a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>📊 System Report</h2>

        <!-- Print Button -->
        <button class="print-btn" onclick="window.print()">
            🖨️ Print Report
        </button>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_properties['total']; ?></h3>
                <p>Total Properties</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $available_properties['total']; ?></h3>
                <p>Available Properties</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $occupied_properties['total']; ?></h3>
                <p>Occupied Properties</p>
            </div>
            <div class="stat-card">
                <h3>PKR <?php echo number_format($total_payments['total'] ?? 0); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_bookings['total']; ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $approved_bookings['total']; ?></h3>
                <p>Approved Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_bookings['total']; ?></h3>
                <p>Pending Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_maintenance['total']; ?></h3>
                <p>Total Maintenance</p>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="section">
            <h3>📋 Recent Bookings</h3>
            <table>
                <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Tenant</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <?php $i=1; while($row = mysqli_fetch_assoc($recent_bookings)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
                    <td>PKR <?php echo number_format($row['price']); ?></td>
                    <td>
                        <span class="status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Recent Payments -->
        <div class="section">
            <h3>💰 Recent Payments</h3>
            <table>
                <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Tenant</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
                <?php $i=1; while($row = mysqli_fetch_assoc($recent_payments)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
                    <td>PKR <?php echo number_format($row['amount']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>