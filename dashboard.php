<?php
session_start();
include('config/db_connection.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

$total_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM properties"));
$available_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM properties WHERE status='available'"));
$occupied_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM properties WHERE status='occupied'"));
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rental_requests"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Hira Rentals</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body{ font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .navbar{ background: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar h2{ color: #E8622A; margin: 0; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .badge { background: #E8622A; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .container{ max-width: 1200px; margin: 35px auto; padding: 0 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #ccc; text-decoration: none; color: inherit; transition: 0.25s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .stat-card.total { border-left-color: #3498db; }
        .stat-card.available { border-left-color: #2ecc71; }
        .stat-card.occupied { border-left-color: #e74c3c; }
        .stat-card.bookings { border-left-color: #9b59b6; }
        .stat-info h3 { font-size: 28px; color: #2c3e50; }
        .stat-info p { font-size: 12px; color: #7f8c8d; text-transform: uppercase; font-weight: bold; }
        .quick-access-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 20px; padding-bottom: 8px; border-bottom: 3px solid #E8622A; display: inline-block; }
        .menu-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
        .menu-card { background: white; padding: 30px 20px; border-radius: 12px; text-align: center; text-decoration: none; color: #2c3e50; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: 0.3s; border: 1px solid #f0f0f0; }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(232, 98, 42, 0.1); }
        .menu-card .icon { font-size: 32px; margin-bottom: 15px; color: #E8622A; }
        .menu-card p { font-weight: 600; font-size: 15px; }
        .logout-btn { background: #2c3e50; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-left: 10px;}
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🏠 Hira Rentals</h2>
        <div class="user-info">
            <span>👤 <strong><?php echo htmlspecialchars($username); ?></strong></span>
            <span class="badge"><?php echo $role; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <a href="properties.php" class="stat-card total">
                <div class="stat-info"><p>Total Properties</p><h3><?php echo $total_properties['total']; ?></h3></div><div>🏠</div>
            </a>
            <a href="properties.php?status=available" class="stat-card available">
                <div class="stat-info"><p>Available</p><h3><?php echo $available_properties['total']; ?></h3></div><div>✅</div>
            </a>
            <a href="properties.php?status=occupied" class="stat-card occupied">
                <div class="stat-info"><p>Occupied</p><h3><?php echo $occupied_properties['total']; ?></h3></div><div>🔒</div>
            </a>
            <a href="booking.php" class="stat-card bookings">
                <div class="stat-info"><p>Total Bookings</p><h3><?php echo $total_bookings['total']; ?></h3></div><div>📊</div>
            </a>
        </div>

        <p class="quick-access-title">Quick Access</p>
        <div class="menu-grid">

            <?php if($role == 'tenant'): ?>
            <a href="properties.php" class="menu-card"><div class="icon">🏢</div><p>Properties</p></a>
            <a href="schedule_visit.php" class="menu-card"><div class="icon">📅</div><p>Schedule Visit</p></a>
            <a href="booking.php" class="menu-card"><div class="icon">📋</div><p>My Bookings</p></a>
            <a href="my_contract.php" class="menu-card"><div class="icon">📜</div><p>My Contract</p></a>
            <a href="booking.php" class="menu-card"><div class="icon">💳</div><p>Make Payment</p></a>
            <a href="payments_history.php" class="menu-card"><div class="icon">💰</div><p>Payment History</p></a>
            <a href="property_status.php" class="menu-card"><div class="icon">📊</div><p>Property Status</p></a>
            <a href="maintenance.php" class="menu-card"><div class="icon">🔧</div><p>Maintenance</p></a>
            <?php endif; ?>

            <?php if($role == 'owner'): ?>
            <a href="properties.php" class="menu-card"><div class="icon">🏢</div><p>Properties</p></a>
            <a href="schedule_visit.php" class="menu-card"><div class="icon">📅</div><p>Schedule Visit</p></a>
            <a href="booking.php" class="menu-card"><div class="icon">📋</div><p>Bookings</p></a>
            <a href="property_status.php" class="menu-card"><div class="icon">📊</div><p>Property Status</p></a>
            <a href="payments_history.php" class="menu-card"><div class="icon">💰</div><p>Payment History</p></a>
            <a href="maintenance.php" class="menu-card"><div class="icon">🔧</div><p>Maintenance</p></a>
            <a href="view_tenant_details.php" class="menu-card"><div class="icon">👥</div><p>Tenant Details</p></a>
            <?php endif; ?>

            <?php if($role == 'manager'): ?>
            <a href="properties.php" class="menu-card"><div class="icon">🏢</div><p>Properties</p></a>
            <a href="schedule_visit.php" class="menu-card"><div class="icon">📅</div><p>Schedule Visit</p></a>
            <a href="booking.php" class="menu-card"><div class="icon">📋</div><p>Bookings</p></a>
            <a href="my_contract.php" class="menu-card"><div class="icon">📜</div><p>Contracts</p></a>
            <a href="maintenance.php" class="menu-card"><div class="icon">🔧</div><p>Maintenance</p></a>
            <a href="generate_reports.php" class="menu-card"><div class="icon">📈</div><p>generate reports</p></a>
            <?php endif; ?>

            <?php if($role == 'admin'): ?>
            <a href="properties.php" class="menu-card"><div class="icon">🏢</div><p>Properties</p></a>
            <a href="manage_users.php" class="menu-card"><div class="icon">👥</div><p>Manage Users</p></a>
            <a href="manage_property_manager.php" class="menu-card"><div class="icon">👨‍💼</div><p>Manage Managers</p></a>
            <a href="manage_property.php" class="menu-card"><div class="icon">🏠</div><p>Manage Properties</p></a>
            <a href="payments_history.php" class="menu-card"><div class="icon">💰</div><p>Payment History</p></a>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>