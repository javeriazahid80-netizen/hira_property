<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['role']); 

if($role == 'manager' && isset($_POST['create_contract'])){
    $property_id = $_POST['property_id'];
    $tenant_id = $_POST['tenant_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $rent_amount = $_POST['rent_amount'];

    $insert = "INSERT INTO lease_agreements 
               (property_id, tenant_id, manager_id, start_date, end_date, rent_amount, status) 
               VALUES 
               ('$property_id','$tenant_id','$user_id','$start_date','$end_date','$rent_amount','Pending')";
    
    if(mysqli_query($conn, $insert)){
        echo "<script>alert('Lease Contract Created Successfully!'); window.location='manage_contracts.php';</script>";
    }
}

if($role == 'tenant' && isset($_GET['action']) && $_GET['action'] == 'sign'){
    $contract_id = $_GET['id'];
    
    $update_contract = "UPDATE lease_agreements 
                        SET status='Signed' 
                        WHERE id='$contract_id' 
                        AND tenant_id='$user_id'";
    
    if(mysqli_query($conn, $update_contract)){
        $get_prop = mysqli_query($conn, 
            "SELECT property_id FROM lease_agreements WHERE id='$contract_id'");
        $prop_row = mysqli_fetch_assoc($get_prop);
        $property_id = $prop_row['property_id'];
        
        mysqli_query($conn, 
            "UPDATE properties SET status='Rented' WHERE id='$property_id'");
        
        echo "<script>alert('Contract Signed Successfully! Property is now Rented.'); window.location='manage_contracts.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lease Contracts - Hira Rentals</title>
    <style>
        body{ font-family: Arial; background: #f5f5f5; margin: 30px; }
        .box{ 
            background: #fff; padding: 25px; border-radius: 10px; 
            border: 1px solid #ddd; max-width: 900px; margin: 0 auto; 
        }
        h2{ color: #E8622A; margin-top: 0; }
        input, select, button{ 
            width: 100%; padding: 10px; margin: 10px 0; 
            border: 1px solid #ccc; border-radius: 6px; 
            box-sizing: border-box; 
        }
        button{ 
            background: #E8622A; color: white; border: none; 
            font-size: 16px; cursor: pointer; 
        }
        table{ 
            width: 100%; border-collapse: collapse; 
            margin-top: 20px; background: white; 
        }
        th, td{ border: 1px solid #ddd; padding: 12px; text-align: left; }
        th{ background-color: #E8622A; color: white; }
        .badge{ 
            padding: 5px 10px; border-radius: 4px; 
            font-weight: bold; font-size: 12px; 
        }
        .Pending{ background: #ffc107; color: #333; }
        .Signed{ background: #28a745; color: white; }
        .btn-sign{ 
            background: #28a745; color: white; padding: 6px 12px; 
            text-decoration: none; border-radius: 4px; 
            font-size: 13px; font-weight: bold; 
        }
    </style>
</head>
<body>

    <div class="box">
        <h2>📄 Lease Agreements / Contracts</h2>
        <a href="dashboard.php" 
           style="color: #E8622A; text-decoration: none; font-size: 14px;">
           ⬅️ Back to Dashboard
        </a>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

        <?php if($role == 'manager'): ?>
            <h3>➕ Create New Lease Contract</h3>
            <form method="POST" style="max-width: 500px; margin-bottom: 40px;">
                <label>Select Property:</label>
                <select name="property_id" required>
                    <?php
                    $props = mysqli_query($conn, 
                        "SELECT id, title FROM properties WHERE status='available'");
                    while($p = mysqli_fetch_assoc($props)){
                        echo "<option value='".$p['id']."'>".$p['title']."</option>";
                    }
                    ?>
                </select>

                <label>Select Tenant:</label>
                <select name="tenant_id" required>
                    <?php
                    $tenants = mysqli_query($conn, 
                        "SELECT id, first_name, last_name FROM users WHERE role='tenant'");
                    while($t = mysqli_fetch_assoc($tenants)){
                        echo "<option value='".$t['id']."'>".$t['first_name']." ".$t['last_name']."</option>";
                    }
                    ?>
                </select>

                <label>Rent Amount (Per Month):</label>
                <input type="number" name="rent_amount" 
                       placeholder="Rs. 30000" required>

                <label>Start Date:</label>
                <input type="date" name="start_date" required>

                <label>End Date:</label>
                <input type="date" name="end_date" required>

                <button type="submit" name="create_contract">
                    Send Contract to Tenant
                </button>
            </form>
        <?php endif; ?>

        <h3>📋 Contracts History</h3>
        <table>
            <tr>
                <th>Property</th>
                <th>Rent Amount</th>
                <th>Duration</th>
                <th>Status</th>
                <?php if($role == 'tenant') echo "<th>Action</th>"; ?>
            </tr>
            <?php
            if($role == 'tenant'){
                $q = "SELECT l.*, p.title 
                      FROM lease_agreements l 
                      JOIN properties p ON l.property_id = p.id 
                      WHERE l.tenant_id = '$user_id'";
            } else {
                $q = "SELECT l.*, p.title 
                      FROM lease_agreements l 
                      JOIN properties p ON l.property_id = p.id 
                      WHERE l.manager_id = '$user_id'";
            }
            
            $res = mysqli_query($conn, $q);
            while($row = mysqli_fetch_assoc($res)){
                echo "<tr>";
                echo "<td>".$row['title']."</td>";
                echo "<td>Rs. ".$row['rent_amount']."</td>";
                echo "<td>".$row['start_date']." to ".$row['end_date']."</td>";
                echo "<td><span class='badge ".$row['status']."'>".$row['status']."</span></td>";
                
                if($role == 'tenant'){
                    echo "<td>";
                    if($row['status'] == 'Pending'){
                        echo "<a href='manage_contracts.php?action=sign&id=".$row['id']."' 
                               class='btn-sign' 
                               onclick='return confirm(\"Do you agree to sign this contract?\")'>
                               ✍️ Sign Contract
                             </a>";
                    } else {
                        echo "✔️ Signed";
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            if(mysqli_num_rows($res) == 0){ 
                echo "<tr><td colspan='5' style='text-align:center;'>No contracts found.</td></tr>"; 
            }
            ?>
        </table>
    </div>

</body>
</html>