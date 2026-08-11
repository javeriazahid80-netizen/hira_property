<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_SESSION['property_id']) ? $_SESSION['property_id'] : null;

if($property_id){
    $prop_query = mysqli_query($conn, "SELECT price FROM properties WHERE id='$property_id'");
    $prop_row = mysqli_fetch_assoc($prop_query);
    $amount = $prop_row ? $prop_row['price'] : 100.00;
} else {
    $property_id = 1;
    $amount = 100.00;
}

$insert_query = "INSERT INTO payments 
          (tenant_id, property_id, amount, status, payment_method) 
          VALUES 
          ('$user_id','$property_id','$amount','Paid','PayFast')";
mysqli_query($conn, $insert_query);

unset($_SESSION['property_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful - Hira Rentals</title>
    <style>
        body{
            font-family: Arial;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card{
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 400px;
            border: 1px solid #eef2f5;
        }
        .tick{ font-size: 60px; margin-bottom: 20px; }
        h2{ color: #28a745; margin-bottom: 10px; }
        p{ color: #718096; margin-bottom: 30px; }
        .btn{
            display: inline-block;
            padding: 12px 30px;
            background: #E8622A;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover{ background: #c94d1a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="tick">✅</div>
        <h2>Payment Successful!</h2>
        <p>Your rent payment has been completed successfully. 
           Click the button below to go back to your dashboard.</p>
        <a href="dashboard.php" class="btn">Go to Dashboard</a>
    </div>
</body>
</html>G