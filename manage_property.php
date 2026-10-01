<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id']) || (strtolower($_SESSION['role']) != 'manager' && strtolower($_SESSION['role']) != 'admin')){
    echo "<h2 style='color:red; text-align:center;'>Access Denied!</h2>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = strtolower($_SESSION['role']);

if(isset($_POST['add_property'])){
    $title = $_POST['title'];
    $price = $_POST['price'];
    $location = $_POST['location'];

    // Latitude/Longitude are optional — if left blank, store NULL instead of an empty string
    $latitude = ($_POST['latitude'] !== '') ? $_POST['latitude'] : null;
    $longitude = ($_POST['longitude'] !== '') ? $_POST['longitude'] : null;

    $stmt = mysqli_prepare($conn, "INSERT INTO properties 
               (title, price, location, latitude, longitude, manager_id, status) 
               VALUES (?, ?, ?, ?, ?, ?, 'available')");
    mysqli_stmt_bind_param($stmt, "sdsddi", $title, $price, $location, $latitude, $longitude, $user_id);

    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('Property Added Successfully!'); window.location='manage_properties.php';</script>";
    } else {
        echo "<script>alert('Error: ".mysqli_error($conn)."');</script>";
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'toggle_status'){
    $property_id = intval($_GET['id']);
    $current_status = $_GET['status'];
    $new_status = ($current_status == 'available') ? 'occupied' : 'available';
    
    if($user_role == 'admin') {
        $stmt = mysqli_prepare($conn, "UPDATE properties SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $property_id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE properties SET status=? WHERE id=? AND manager_id=?");
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $property_id, $user_id);
    }
    mysqli_stmt_execute($stmt);
    header("Location: manage_properties.php");
    exit();
}

if(isset($_GET['action']) && $_GET['action'] == 'delete'){
    $property_id = intval($_GET['id']);
    
    if($user_role == 'admin') {
        $stmt = mysqli_prepare($conn, "DELETE FROM properties WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $property_id);
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM properties WHERE id=? AND manager_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $property_id, $user_id);
    }
    
    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('Property Deleted!'); window.location='manage_properties.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Properties - Hira Rentals</title>
    <style>
        body{ font-family: Arial; background: #f5f5f5; margin: 30px; }
        .box{ 
            background: #fff; padding: 25px; border-radius: 10px; 
            border: 1px solid #ddd; max-width: 900px; margin: 0 auto; 
        }
        h2{ color: #E8622A; margin-top: 0; }
        input, button{ 
            width: 100%; padding: 10px; margin: 10px 0; 
            border: 1px solid #ccc; border-radius: 6px; 
            box-sizing: border-box; 
        }
        button{ 
            background: #E8622A; color: white; 
            border: none; font-size: 16px; cursor: pointer; 
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
        .available{ background: #28a745; color: white; }
        .occupied{ background: #dc3545; color: white; }
        .btn-action{ 
            padding: 5px 10px; text-decoration: none; 
            color: white; border-radius: 4px; 
            font-size: 12px; margin-right: 5px; 
        }
        .btn-status{ background: #007bff; }
        .btn-del{ background: #6c757d; }
        .coord-row{ display: flex; gap: 10px; }
        .coord-row input{ margin: 10px 0; }
        .hint{
            font-size: 12px; color: #888; margin: -6px 0 10px;
        }
        .hint a{ color: #E8622A; }
    </style>
</head>
<body>

    <div class="box">
        <h2>🛠️ Manage Properties</h2>
        <a href="dashboard.php" 
           style="color: #E8622A; text-decoration: none; font-size: 14px;">
           ⬅️ Back to Dashboard
        </a>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

        <h3>➕ Add New Property</h3>
        <form method="POST" style="max-width: 500px; margin-bottom: 30px;">
            <input type="text" name="title" 
                   placeholder="Property Title" required>
            <input type="number" name="price" 
                   placeholder="Rent Price" required>
            <input type="text" name="location" 
                   placeholder="Location (e.g. Civil Lines Gujrat)" required>

            <div class="coord-row">
                <input type="text" name="latitude" 
                       placeholder="Latitude (e.g. 32.5742)" pattern="^-?\d{1,3}(\.\d+)?$">
                <input type="text" name="longitude" 
                       placeholder="Longitude (e.g. 74.0856)" pattern="^-?\d{1,3}(\.\d+)?$">
            </div>
            <p class="hint">
                Optional — leave blank if unknown. Get exact coordinates from
                <a href="https://maps.google.com" target="_blank" rel="noopener">Google Maps</a>:
                right-click the property location → "What's here?" → copy the two numbers shown.
            </p>

            <button type="submit" name="add_property">
                Add Property
            </button>
        </form>

        <h3>📋 Properties List</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Location</th>
                <th>Coordinates</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            if($user_role == 'admin') {
                $res = mysqli_query($conn, "SELECT * FROM properties");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT * FROM properties WHERE manager_id = ?");
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
            }
            
            while($row = mysqli_fetch_assoc($res)){
                echo "<tr>";
                echo "<td>".htmlspecialchars($row['title'])."</td>";
                echo "<td>Rs. ".htmlspecialchars($row['price'])."</td>";
                echo "<td>".htmlspecialchars($row['location'])."</td>";
                echo "<td>";
                if(!empty($row['latitude']) && !empty($row['longitude'])){
                    echo "<span style='color:#28a745; font-size:12px;'>✓ ".htmlspecialchars($row['latitude']).", ".htmlspecialchars($row['longitude'])."</span>";
                } else {
                    echo "<span style='color:#bbb; font-size:12px;'>Not set</span>";
                }
                echo "</td>";
                echo "<td><span class='badge ".htmlspecialchars($row['status'])."'>".ucfirst($row['status'])."</span></td>";
                echo "<td>";
                echo "<a href='manage_properties.php?action=toggle_status&id=".$row['id']."&status=".urlencode($row['status'])."' 
                           class='btn-action btn-status'>
                           Change Status
                       </a>";
                echo "<a href='manage_properties.php?action=delete&id=".$row['id']."' 
                           class='btn-action btn-del' 
                           onclick='return confirm(\"Delete this property?\")'>
                           Delete
                       </a>";
                echo "</td>";
                echo "</tr>";
            }
            if(mysqli_num_rows($res) == 0){ 
                echo "<tr><td colspan='6' style='text-align:center;'>No properties found.</td></tr>"; 
            }
            ?>
        </table>
    </div>

</body>
</html>
