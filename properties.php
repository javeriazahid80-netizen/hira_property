<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_property'])){
    $title = $_POST['title'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    
    $image_name = "";
    if(isset($_FILES['property_image']) && $_FILES['property_image']['error'] == 0){
        $image_name = time() . '_' . $_FILES['property_image']['name'];
        $target_path = "uploads/" . $image_name;
        move_uploaded_file($_FILES['property_image']['tmp_name'], $target_path);
    }

    $query = "INSERT INTO properties 
              (title, location, price, status, manager_id, image) 
              VALUES 
              ('$title','$location','$price','$status','$user_id','$image_name')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Property Added Successfully!'); window.location.href='properties.php';</script>";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_property'])){
    $id = $_POST['property_id'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $image_update = "";
    if(isset($_FILES['property_image']) && 
       $_FILES['property_image']['error'] == 0){
        $image_name = time() . '_' . $_FILES['property_image']['name'];
        $target_path = "uploads/" . $image_name;
        move_uploaded_file($_FILES['property_image']['tmp_name'], $target_path);
        $image_update = ", image='$image_name'";
    }

    $query = "UPDATE properties 
              SET title='$title', location='$location',
              price='$price', status='$status'
              $image_update
              WHERE id='$id'";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Property Updated Successfully!'); window.location.href='properties.php';</script>";
    }
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $query = "DELETE FROM properties WHERE id='$id'";
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Property Deleted!'); window.location.href='properties.php';</script>";
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_price = isset($_GET['price']) ? $_GET['price'] : '';

$query = "SELECT * FROM properties WHERE 1=1";

if($search != ''){
    $query .= " AND (title LIKE '%$search%' 
                OR location LIKE '%$search%')";
}
if($filter_status != ''){
    $query .= " AND status = '$filter_status'";
}
if($filter_price != ''){
    $query .= " AND price <= '$filter_price'";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Properties - Hira Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{
            font-family: 'Poppins', Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        .navbar{
            background: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eef2f5;
        }
        .navbar h2{ color: #E8622A; margin: 0; }
        .container{
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .add-form{
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            border: 1px solid #eef2f5;
        }
        .add-form input,
        .add-form select{
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 180px;
            font-family: inherit;
        }
        .add-form button{
            padding: 10px 25px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .search-bar{
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .search-bar input,
        .search-bar select{
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        .search-bar button{
            padding: 12px 25px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .grid{
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .card{
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eef2f5;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
        }
        .property-img{
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #eaeaea;
        }
        .card-content{ padding: 20px; flex-grow: 1; }
        .card h3{ margin: 0 0 8px 0; font-size: 18px; color: #2d3748; }
        .card p{ margin: 5px 0; color: #718096; font-size: 14px; }
        .price{ color: #E8622A; font-weight: 600; font-size: 20px; margin: 10px 0 !important; }
        .status-badge{ display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-available{ background: #d4edda; color: #155724; }
        .status-occupied{ background: #f8d7da; color: #721c24; }
        .btn-details{ display: inline-block; padding: 10px 20px; background: #E8622A; color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px; text-align: center; }
        .action-buttons{ margin-top: 15px; border-top: 1px solid #f7fafc; padding-top: 12px; }
        .btn-delete{ background: #dc3545; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
        .btn-edit{ background: #ffc107; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; margin-right: 5px; }
        .btn-back{ background: #34495e; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; display: inline-block; margin-right: 10px; }
        .logout{ background: #E8622A; color: #fff; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .no-data{ text-align: center; padding: 50px; color: #999; grid-column: span 3; }
        .edit-form{ margin-top: 15px; display: none; background: #f7fafc; padding: 15px; border-radius: 8px; }
        .edit-form input, 
        .edit-form select{ 
            width: 100%; padding: 8px; margin: 5px 0; 
            border: 1px solid #ddd; border-radius: 6px; 
            box-sizing: border-box; 
        }
        .edit-form button{ 
            width: 100%; padding: 10px; background: #E8622A; 
            color: #fff; border: none; border-radius: 6px; 
            cursor: pointer; margin-top: 8px; 
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

        <?php if($role == 'manager' || $role == 'owner'): ?>
        <div class="add-form">
            <h3>Add New Property</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" 
                       placeholder="Property Title" required>
                <input type="text" name="location" 
                       placeholder="Location" required>
                <input type="number" name="price" 
                       placeholder="Price (PKR)" required>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                </select>
                <input type="file" name="property_image" 
                       accept="image/*" style="width:230px;">
                <button type="submit" name="add_property">
                    + Add Property
                </button>
            </form>
        </div>
        <?php endif; ?>

        <form method="GET">
            <div class="search-bar">
                <input type="text" name="search" 
                       placeholder="Search by name or location..."
                       value="<?php echo $search; ?>">
                <select name="status">
                    <option value="">Any Status</option>
                    <option value="available" 
                        <?php if($filter_status=='available') echo 'selected'; ?>>
                        Available
                    </option>
                    <option value="occupied" 
                        <?php if($filter_status=='occupied') echo 'selected'; ?>>
                        Occupied
                    </option>
                </select>
                <input type="number" name="price" 
                       placeholder="Max Price"
                       value="<?php echo $filter_price; ?>">
                <button type="submit">Search</button>
            </div>
        </form>

        <div class="grid">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <?php 
                    $image_filename = (!empty($row['image'])) ? $row['image'] : '';
                    if(!empty($image_filename) && file_exists("uploads/" . $image_filename)){
                        $image_path = "uploads/" . $image_filename;
                    } else {
                        $image_path = "https://placehold.co/600x400/eaeaea/718096?text=Hira+Rentals";
                    }
                    ?>
                    <img src="<?php echo $image_path; ?>" 
                         class="property-img" alt="House Image">

                    <div class="card-content">
                        <h3><?php echo $row['title']; ?></h3>
                        <p>📍 <?php echo $row['location']; ?></p>
                        <p class="price">
                            PKR <?php echo number_format($row['price']); ?>/mo
                        </p>
                        <div class="status-badge status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </div>
                        <br><br>
                        <a href="property_detail.php?id=<?php echo $row['id']; ?>" 
                           class="btn-details">
                            Details
                        </a>

                        <?php if($role == 'manager' || $role == 'owner'): ?>
                        <div class="action-buttons">
                            <button class="btn-edit" 
                                onclick="toggleEdit('edit-<?php echo $row['id']; ?>')">
                                Edit
                            </button>
                            <a href="?delete=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete this property?')" 
                               class="btn-delete" 
                               style="text-decoration:none">
                                Delete
                            </a>
                        </div>

                        <div class="edit-form" 
                             id="edit-<?php echo $row['id']; ?>">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="property_id" 
                                       value="<?php echo $row['id']; ?>">
                                <input type="text" name="title" 
                                       value="<?php echo $row['title']; ?>" required>
                                <input type="text" name="location" 
                                       value="<?php echo $row['location']; ?>" required>
                                <input type="number" name="price" 
                                       value="<?php echo $row['price']; ?>" required>
                                <select name="status">
                                    <option value="available" 
                                        <?php if($row['status']=='available') echo 'selected'; ?>>
                                        Available
                                    </option>
                                    <option value="occupied"
                                        <?php if($row['status']=='occupied') echo 'selected'; ?>>
                                        Occupied
                                    </option>
                                </select>
                                <label style="font-size:12px; color:#666;">
                                    Update Image (Optional):
                                </label>
                                <input type="file" name="property_image" 
                                       accept="image/*">
                                <button type="submit" name="update_property">
                                    Update Property
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    No properties found!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleEdit(id){
        var form = document.getElementById(id);
        if(form.style.display == 'none' || 
           form.style.display == ''){
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
    </script>

</body>
</html>