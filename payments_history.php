<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$role = strtolower($_SESSION['role']);
$user_id = $_SESSION['user_id'];

if ($role == 'manager' || $role == 'owner' || $role == 'admin') {
    $query = "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as tenant_name, pr.title as property_title 
              FROM payments p
              JOIN users u ON p.tenant_id = u.id
              JOIN properties pr ON p.property_id = pr.id
              ORDER BY p.payment_date DESC";
} else {
    $query = "SELECT p.*, pr.title as property_title 
              FROM payments p
              JOIN properties pr ON p.property_id = pr.id
              WHERE p.tenant_id = '$user_id'
              ORDER BY p.payment_date DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment History - Hira Rentals</title>
    <style>
        body{
            font-family: Arial, sans-serif;
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
        .history-card{
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .history-card h3{
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #E8622A;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        tr:hover {
            background-color: #fdf6f2;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
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
        .btn-back:hover{
            background: #2c3e50;
        }
        .logout{
            background: #E8622A;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .no-data{
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Hira Rentals</h2>
        <div>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="history-card">
            <h3>📜 Rent Payment History</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if($role == 'manager' || $role == 'owner' || $role == 'admin'): ?>
                            <th>Tenant Name</th>
                        <?php endif; ?>
                        <th>Property</th>
                        <th>Amount (PKR)</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <?php if($role == 'manager' || $role == 'owner' || $role == 'admin'): ?>
                                <td><strong><?php echo isset($row['tenant_name']) ? $row['tenant_name'] : 'N/A'; ?></strong></td>
                            <?php endif; ?>
                            <td><?php echo $row['property_title']; ?></td>
                            <td style="color: #E8622A; font-weight: bold;">
                                PKR <?php echo number_format($row['amount']); ?>
                            </td>
                            <td>
                                <span class="status-paid"><?php echo $row['status']; ?></span>
                            </td>
                            <td><?php echo date('d M, Y', strtotime($row['payment_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($role == 'manager' || $role == 'owner' || $role == 'admin') ? '6' : '5'; ?>" class="no-data">
                                No payment records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>