<?php
session_start();
include('config/db_connection.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if(isset($_POST['status']) && isset($_POST['booking_id']) && $role != 'tenant') {
    $id = $_POST['booking_id'];
    $status = $_POST['status'];
    $property_id = $_POST['property_id'];

    if(empty($property_id) && !empty($id)){
        $fetch = mysqli_query($conn, "SELECT property_id FROM rental_requests WHERE id='$id'");
        $row_fetch = mysqli_fetch_assoc($fetch);
        $property_id = $row_fetch['property_id'];
    }

    mysqli_query($conn, "UPDATE rental_requests SET status = '$status' WHERE id = '$id'");
    
    $prop_status = ($status == 'rejected') ? 'available' : 'occupied';
    mysqli_query($conn, "UPDATE properties SET status = '$prop_status' WHERE id = '$property_id'");
    
    header("Location: booking.php");
    exit();
}

if($role == 'tenant') {
    $query = "SELECT r.*, p.title, p.location, p.price, r.property_id 
              FROM rental_requests r 
              JOIN properties p ON r.property_id = p.id 
              WHERE r.tenant_id = '$user_id' 
              ORDER BY r.created_at DESC";
} else {
    $query = "SELECT r.*, p.title, p.location, p.price, u.first_name, r.property_id 
              FROM rental_requests r 
              JOIN properties p ON r.property_id = p.id
              JOIN users u ON r.tenant_id = u.id
              ORDER BY r.created_at DESC";
}

$result = mysqli_query($conn, $query);

function getPaymentCell($conn, $user_id, $row){
    $html = "";
    
    if(strtolower($row['status']) == 'approved'){
        
        $contract_check = mysqli_query($conn, 
            "SELECT status FROM lease_agreements 
             WHERE tenant_id = '$user_id' 
             AND property_id = '".$row['property_id']."' 
             ORDER BY id DESC LIMIT 1");
        $contract_row = mysqli_fetch_assoc($contract_check);

        $pay_check = mysqli_query($conn, 
            "SELECT id FROM payments 
             WHERE tenant_id = '$user_id' 
             AND property_id = '".$row['property_id']."' 
             AND status = 'Paid' 
             LIMIT 1");

        if(mysqli_num_rows($pay_check) > 0){
            $html = '<span class="paid-badge">✓ Paid</span>';
        }
        elseif($contract_row && $contract_row['status'] == 'Signed'){
            $html = '<a href="payments.php?property_id='.$row['property_id'].'&amount='.$row['price'].'" class="btn pay">Pay Now</a>';
        }
        elseif($contract_row && $contract_row['status'] == 'Pending'){
            $html = '<span class="waiting-badge">⏳ Sign Contract First</span><a href="my_contract.php" class="link-contract">Go to My Contract →</a>';
        }
        else {
            $html = '<span class="waiting-badge">⏳ Waiting for Contract</span>';
        }
    } else {
        $html = '<span style="color:#999">N/A</span>';
    }
    
    return $html;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Hira Rentals</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; }
        .navbar{
            background: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .navbar h2{ color: #E8622A; margin: 0; }
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
        .container { max-width: 1100px; margin: 35px auto; padding: 0 25px; }
        .section-title{
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 3px solid #E8622A;
            display: inline-block;
        }
        table { 
            width: 100%; 
            background: #fff; 
            border-radius: 12px; 
            border-collapse: collapse; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.04); 
        }
        th { background: #E8622A; color: white; padding: 14px 16px; text-align: left; font-size: 13px; text-transform: uppercase; }
        td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        tr:hover td{ background: #fff5f0; }
        .btn { padding: 6px 12px; border: none; cursor: pointer; color: white; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px; }
        .approve { background: #28a745; }
        .reject { background: #dc3545; }
        .pay {
            background: #E8622A;
            padding: 7px 16px;
            border-radius: 6px;
            font-weight: 500;
        }
        .pay:hover {
            background: #c94d1a;
        }
        .paid-badge {
            background: #d4edda;
            color: #155724;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .waiting-badge {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .link-contract {
            color: #E8622A;
            font-size: 12px;
            text-decoration: underline;
            display: block;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>🏠 Hira Rentals</h2>
        <div>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <p class="section-title">📋 My Bookings</p>
        <table>
            <tr>
                <th>Property</th>
                <th>Location</th>
                <th>Price</th>
                <th>Tenant</th>
                <th>Status</th>
                <th>Date</th>
                <?php if($role != 'tenant'): ?><th>Action</th><?php endif; ?>
                <?php if($role == 'tenant'): ?><th>Payment</th><?php endif; ?>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['location']; ?></td>
                <td>PKR <?php echo number_format($row['price']); ?></td>
                <td><?php echo ($role == 'tenant') ? 'You' : $row['first_name']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                <?php if($role != 'tenant'): ?>
                <td>
                    <?php if(strtolower($row['status']) == 'pending'): ?>
                    <form method="POST" action="booking.php" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>">
                        <button name="status" value="approved" class="btn approve">Approve</button>
                        <button name="status" value="rejected" class="btn reject">Reject</button>
                    </form>
                    <?php else: ?>
                    <span style="color:#999">Done</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <?php if($role == 'tenant'): ?>
                <td><?php echo getPaymentCell($conn, $user_id, $row); ?></td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>