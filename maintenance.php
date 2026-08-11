<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_request'])){
    $property_id = $_POST['property_id'];
    $issue = $_POST['issue'];

    $query = "INSERT INTO maintenance 
              (tenant_id, property_id, issue, status) 
              VALUES 
              ('$user_id','$property_id','$issue','pending')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Maintenance Request Sent!'); window.location.href='maintenance.php';</script>";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])){
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    $query = "UPDATE maintenance 
              SET status='$status' 
              WHERE id='$request_id'";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Status Updated!'); window.location.href='maintenance.php';</script>";
    }
}

if($role == 'tenant'){
    $query = "SELECT m.*, p.title, p.location 
              FROM maintenance m 
              JOIN properties p ON m.property_id = p.id
              WHERE m.tenant_id = '$user_id'
              ORDER BY m.created_at DESC";
} else {
    $query = "SELECT m.*, p.title, p.location,
              u.first_name, u.last_name
              FROM maintenance m 
              JOIN properties p ON m.property_id = p.id
              JOIN users u ON m.tenant_id = u.id
              ORDER BY m.created_at DESC";
}

$result = mysqli_query($conn, $query);
$properties = mysqli_query($conn, "SELECT * FROM properties");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maintenance - Hira Rentals</title>
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
        .navbar h2{ color: #E8622A; margin: 0; }
        .container{ padding: 30px; }
        .send-form{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .send-form select,
        .send-form textarea{
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 300px;
        }
        .send-form button{
            padding: 10px 20px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 5px;
        }
        .grid{
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 15px;
        }
        .card{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .card h3{ margin: 0 0 10px 0; }
        .progress-bar{
            background: #eee;
            border-radius: 10px;
            height: 8px;
            margin: 10px 0;
        }
        .progress-fill{ height: 8px; border-radius: 10px; }
        .pending-fill{ background: #ffc107; width: 20%; }
        .in_progress-fill{ background: #E8622A; width: 60%; }
        .completed-fill{ background: #28a745; width: 100%; }
        .status-pending{
            background: #fff3cd;
            color: #856404;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-in_progress{
            background: #ffe5d0;
            color: #E8622A;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-completed{
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .update-form select{
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 10px;
        }
        .update-form button{
            padding: 5px 10px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 5px;
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
        .btn-back:hover{ background: #2c3e50; }
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
            padding: 50px;
            color: #999;
            grid-column: span 2;
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
        <h2>Request Tracker</h2>

        <?php if($role == 'tenant'): ?>
        <div class="send-form">
            <h3>+ New Request</h3>
            <form method="POST">
                <select name="property_id" required>
                    <option value="">Select Property</option>
                    <?php while($p = mysqli_fetch_assoc($properties)): ?>
                    <option value="<?php echo $p['id']; ?>">
                        <?php echo $p['title'].' - '.$p['location']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <br>
                <textarea name="issue" 
                          placeholder="Describe the issue..." 
                          required rows="3"></textarea>
                <br>
                <button type="submit" name="send_request">
                    Submit Request
                </button>
            </form>
        </div>
        <?php endif; ?>

        <div class="grid">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <h3><?php echo $row['issue']; ?></h3>
                    <?php if($role != 'tenant'): ?>
                    <p>👤 <?php echo $row['first_name'].' '.$row['last_name']; ?></p>
                    <?php endif; ?>
                    <p>🏠 <?php echo $row['title'].', '.$row['location']; ?></p>
                    <p>📅 <?php echo date('Y-m-d', strtotime($row['created_at'])); ?></p>

                    <div class="progress-bar">
                        <div class="progress-fill 
                            <?php echo $row['status']; ?>-fill">
                        </div>
                    </div>

                    <span class="status-<?php echo $row['status']; ?>">
                        <?php echo ucfirst(str_replace('_',' ',$row['status'])); ?>
                    </span>

                    <?php if($role == 'manager' || $role == 'owner'): ?>
                    <form method="POST" class="update-form">
                        <input type="hidden" name="request_id" 
                               value="<?php echo $row['id']; ?>">
                        <select name="status">
                            <option value="pending" 
                                <?php if($row['status']=='pending') echo 'selected'; ?>>
                                Pending
                            </option>
                            <option value="in_progress" 
                                <?php if($row['status']=='in_progress') echo 'selected'; ?>>
                                In Progress
                            </option>
                            <option value="completed" 
                                <?php if($row['status']=='completed') echo 'selected'; ?>>
                                Completed
                            </option>
                        </select>
                        <button type="submit" name="update_status">
                            Update
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    No maintenance requests found!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>