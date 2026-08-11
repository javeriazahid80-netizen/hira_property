<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != 'owner'){
    header("Location: dashboard.php");
    exit();
}

// Fetch All Tenants
$query = "SELECT u.id, u.first_name, u.last_name, 
          u.email, u.phone
          FROM users u
          WHERE u.role = 'tenant'
          ORDER BY u.id DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenant Details - Hira Rentals</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body{
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
        }
        .navbar{
            background: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .navbar h2{
            color: #E8622A;
            margin: 0;
        }
        .container{
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 25px;
        }
        .section-title{
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 3px solid #E8622A;
            display: inline-block;
        }
        table{
            width: 100%;
            background: #fff;
            border-radius: 12px;
            border-collapse: collapse;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        th{
            background: #E8622A;
            color: #fff;
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
        }
        td{
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        tr:hover td{
            background: #fff5f0;
        }
        .btn-back{
            background: #2c3e50;
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            margin-right: 10px;
        }
        .logout{
            background: #E8622A;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .no-data{
            text-align: center;
            padding: 50px;
            color: #999;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>🏠 Hira Rentals</h2>
        <div>
            <a href="dashboard.php" class="btn-back">
                ← Back to Dashboard
            </a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <p class="section-title">👥 Tenant Details</p>

        <table>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
            </tr>

            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; 
                while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['first_name']; ?></td>
                    <td><?php echo $row['last_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone'] ?? 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">
                        No tenants found!
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>
</html>