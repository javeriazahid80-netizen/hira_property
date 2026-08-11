<?php
session_start();
include 'config/db_connection.php';

if(!isset($_GET['id'])){
    header("Location: properties.php");
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM properties WHERE id='$id'";
$result = mysqli_query($conn, $query);
$property = mysqli_fetch_assoc($result);

if(!$property){
    header("Location: properties.php");
    exit();
}

$message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_now'])){
    if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'tenant'){
        $tenant_id = $_SESSION['user_id'];
        $property_id = $id;

        $check = mysqli_query($conn, 
            "SELECT * FROM rental_requests 
             WHERE property_id='$property_id' 
             AND tenant_id='$tenant_id' 
             AND status='pending'");

        if(mysqli_num_rows($check) > 0){
            $message = "<script>alert('You already have a pending request for this property!');</script>";
        } else {
            $insert = "INSERT INTO rental_requests 
                       (property_id, tenant_id, status) 
                       VALUES 
                       ('$property_id','$tenant_id','pending')";
            
            if(mysqli_query($conn, $insert)){
                $message = "<script>alert('Booking Request Sent Successfully!'); window.location='booking.php';</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $property['title']; ?> - Hira Rentals</title>
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
        .logout{
            background: #E8622A;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
        }
        .container{
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .property-card{
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .property-card h1{
            color: #333;
            margin-top: 0;
        }
        .price{
            color: #E8622A;
            font-size: 28px;
            font-weight: bold;
        }
        .status-available{
            background: #d4edda;
            color: #155724;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .status-occupied{
            background: #f8d7da;
            color: #721c24;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .info{
            margin: 20px 0;
            line-height: 2;
        }
        .btn{
            display: inline-block;
            padding: 12px 25px;
            background: #E8622A;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-outline{
            background: #fff;
            color: #E8622A;
            border: 1px solid #E8622A;
        }
        .btn-visit{ background: #34495e; }
        .back{
            color: #E8622A;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .steps-guide{
            background: #fff5f0;
            border: 1px solid #E8622A;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .steps-guide h4{
            margin: 0 0 8px 0;
            color: #E8622A;
            font-size: 14px;
        }
        .steps-guide ol{
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.8;
        }
        .map-section{
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .map-section h3{
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 16px;
        }
        .map-section iframe{
            border: 0;
            border-radius: 10px;
            width: 100%;
            height: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <?php echo $message; ?>
    <div class="navbar">
        <h2>Hira Rentals</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            &nbsp;&nbsp;
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <a href="properties.php" class="back">
            ← Back to Properties
        </a>

        <div class="property-card">
            <h1><?php echo $property['title']; ?></h1>

            <span class="status-<?php echo $property['status']; ?>">
                <?php echo ucfirst($property['status']); ?>
            </span>

            <div class="info">
                <p>📍 <b>Location:</b> <?php echo $property['location']; ?></p>
                <p>💰 <b>Price:</b>
                    <span class="price">
                        PKR <?php echo number_format($property['price']); ?>/mo
                    </span>
                </p>
                <p>📅 <b>Listed On:</b>
                    <?php echo date('d M Y',
                        strtotime($property['created_at'])); ?>
                </p>
            </div>

            <?php if(isset($_SESSION['role']) &&
                $_SESSION['role'] == 'tenant' &&
                $property['status'] == 'available'): ?>

            <div class="steps-guide">
                <h4>📋 How to Rent This Property:</h4>
                <ol>
                    <li>Schedule a Visit to see the property in person</li>
                    <li>Once visited, send a Rental Request</li>
                    <li>Manager will create a Lease Contract for you</li>
                    <li>Sign the contract & make your payment</li>
                </ol>
            </div>

            <a href="schedule_visit.php" class="btn btn-visit">
                📅 Schedule Visit
            </a>

            <form method="POST" style="display:inline;">
                <button type="submit" name="book_now" class="btn">
                    📝 Book Now
                </button>
            </form>

            <?php endif; ?>

            <a href="properties.php" class="btn btn-outline">
                Back
            </a>

            <!-- Google Maps Location -->
            <?php if(!empty($property['location'])): ?>
            <div class="map-section">
                <h3>📍 View on Map</h3>
                <iframe
                    loading="lazy"
                    allowfullscreen
                    src="https://maps.google.com/maps?q=<?php echo urlencode($property['location'] . ', Pakistan'); ?>&output=embed">
                </iframe>
            </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html