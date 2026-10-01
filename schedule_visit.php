<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['role']); 

// If we arrived from a specific property's "Schedule Visit" button
// (schedule_visit.php?property_id=13), lock the form to that property
// so the tenant can't accidentally pick a different one from a dropdown.
$locked_property = null;
if($role == 'tenant' && isset($_GET['property_id'])){
    $locked_id = intval($_GET['property_id']);
    $lock_stmt = mysqli_prepare($conn, "SELECT id, title FROM properties WHERE id=?");
    mysqli_stmt_bind_param($lock_stmt, "i", $locked_id);
    mysqli_stmt_execute($lock_stmt);
    $lock_result = mysqli_stmt_get_result($lock_stmt);
    $locked_property = mysqli_fetch_assoc($lock_result);
}

// 1. Tenant Request Submit Handle Karein
if($role == 'tenant' && isset($_POST['submit_visit'])){
    $property_id = intval($_POST['property_id']);
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];

    $stmt = mysqli_prepare($conn, "INSERT INTO site_visits (property_id, tenant_id, visit_date, visit_time) 
               VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiss", $property_id, $user_id, $visit_date, $visit_time);
    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('House Visit Requested Successfully!'); window.location='schedule_visit.php';</script>";
    }
}

// 2. Owner/Manager Status Update Handle Karein
if(($role == 'owner' || $role == 'manager') && isset($_GET['action']) && $_GET['action'] != 'delete'){
    $visit_id = intval($_GET['id']);
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';
    
    $stmt = mysqli_prepare($conn, "UPDATE site_visits SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $visit_id);
    mysqli_stmt_execute($stmt);
    header("Location: schedule_visit.php");
    exit();
}

// 3. 🗑️ DELETE HANDLE KAREIN (Yeh Naya Add Kiya Hai Test Karne Ke Liye)
if(($role == 'owner' || $role == 'manager') && isset($_GET['action']) && $_GET['action'] == 'delete'){
    $visit_id = intval($_GET['id']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM site_visits WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $visit_id);
    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('Visit Request Deleted Successfully!'); window.location='schedule_visit.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>House Visits - Hira Rentals</title>
    <style>
        body{ font-family: Arial; background: #f5f5f5; margin: 30px; }
        .box{ background: #fff; padding: 25px; border-radius: 10px; border: 1px solid #ddd; max-width: 600px; margin: 0 auto; }
        h2 { color: #E8622A; margin-top: 0; }
        input, select, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        input[disabled]{ background: #f0f0f0; color: #555; }
        button { background: #E8622A; color: white; border: none; font-size: 16px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #E8622A; color: white; }
        .badge { padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .Pending { background: #ffc107; color: #333; }
        .Approved { background: #28a745; color: white; }
        .Rejected { background: #dc3545; color: white; }
        .btn-action { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; font-size: 12px; margin-right: 5px; }
        .btn-app { background: #28a745; }
        .btn-rej { background: #dc3545; }
        .btn-del { background: #6c757d; } /* Grey color for Delete */
        .locked-note{ font-size: 12px; color: #888; margin: -6px 0 4px; }
    </style>
</head>
<body>

    <div class="box" style="max-width: 900px;">
        <h2>🏠 House Visit Scheduler</h2>
        <a href="dashboard.php" style="color: #E8622A; text-decoration: none; font-size: 14px;">⬅️ Back to Dashboard</a>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

        <?php if($role == 'tenant'): ?>
            <h3>Request a New House Visit</h3>
            <form method="POST">
                <?php if($locked_property): ?>
                    <label>Property:</label>
                    <input type="text" value="<?php echo htmlspecialchars($locked_property['title']); ?>" disabled>
                    <p class="locked-note">This visit will be booked for the property you came from. To visit a different property, open its page and click "Schedule Visit" there instead.</p>
                    <input type="hidden" name="property_id" value="<?php echo $locked_property['id']; ?>">
                <?php else: ?>
                    <label>Select Property:</label>
                    <select name="property_id" required>
                        <?php
                        $props = mysqli_query($conn, "SELECT id, title FROM properties");
                        while($p = mysqli_fetch_assoc($props)){
                            echo "<option value='".$p['id']."'>".htmlspecialchars($p['title'])."</option>";
                        }
                        ?>
                    </select>
                <?php endif; ?>

                <label>Select Date:</label>
                <input type="date" name="visit_date" required min="<?php echo date('Y-m-d'); ?>">

                <label>Select Time:</label>
                <input type="time" name="visit_time" required>

                <button type="submit" name="submit_visit">Request Visit</button>
            </form>
        <?php endif; ?>

        <h3>Scheduled House Visits History</h3>
        <table>
            <tr>
                <th>Property</th>
                <?php if($role != 'tenant') echo "<th>Requested By</th>"; ?>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <?php if($role == 'owner' || $role == 'manager') echo "<th>Actions</th>"; ?>
            </tr>
            <?php
            if($role == 'tenant'){
                $stmt = mysqli_prepare($conn, "SELECT v.*, p.title FROM site_visits v JOIN properties p ON v.property_id = p.id WHERE v.tenant_id = ?");
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
            } else {
                $res = mysqli_query($conn, "SELECT v.*, p.title, u.first_name, u.last_name, u.email, u.phone FROM site_visits v 
                      JOIN properties p ON v.property_id = p.id 
                      JOIN users u ON v.tenant_id = u.id");
            }
            
            while($row = mysqli_fetch_assoc($res)){
                echo "<tr>";
                echo "<td>".htmlspecialchars($row['title'])."</td>";
                
                if($role != 'tenant') {
                    echo "<td>";
                    echo "<b>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</b><br>";
                    echo "<span style='color: #666; font-size: 12px; display: inline-block; margin-top: 4px;'>";
                    echo "📧 " . htmlspecialchars($row['email']) . "<br>📞 " . htmlspecialchars($row['phone']);
                    echo "</span>";
                    echo "</td>";
                }
                
                echo "<td>".$row['visit_date']."</td>";
                echo "<td>".$row['visit_time']."</td>";
                echo "<td><span class='badge ".$row['status']."'>".$row['status']."</span></td>";
                
                if($role == 'owner' || $role == 'manager'){
                    echo "<td>";
                    if($row['status'] == 'Pending'){
                        echo "<a href='schedule_visit.php?action=approve&id=".$row['id']."' class='btn-action btn-app'>Approve</a>";
                        echo "<a href='schedule_visit.php?action=reject&id=".$row['id']."' class='btn-action btn-rej'>Reject</a>";
                    }
                    // 🗑️ Delete button will always be visible,for testing purposes
                    echo "<a href='schedule_visit.php?action=delete&id=".$row['id']."' class='btn-action btn-del' onclick='return confirm(\"Are you sure you want to delete this?\")'>Delete</a>";
                    echo "</td>"; 
                }
                echo "</tr>";
            }
            if(mysqli_num_rows($res) == 0) { echo "<tr><td colspan='6' style='text-align:center;'>No visits found.</td></tr>"; }
            ?>
        </table>
    </div>

</body>
</html>
