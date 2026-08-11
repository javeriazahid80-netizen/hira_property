<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != 'admin'){
    header("Location: dashboard.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND role='manager'");
    echo "<script>alert('Manager Deleted!'); window.location.href='manage_property_manager.php';</script>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_manager'])){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = md5($_POST['password']);

    $query = "INSERT INTO users 
              (first_name, last_name, email, phone, role, password) 
              VALUES 
              ('$first_name','$last_name','$email','$phone','manager','$password')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Manager Added!'); window.location.href='manage_property_manager.php';</script>";
    } else {
        echo "<script>alert('Email already exists!');</script>";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_manager'])){
    $id = $_POST['manager_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $query = "UPDATE users 
              SET first_name='$first_name', 
              last_name='$last_name',
              email='$email', 
              phone='$phone'
              WHERE id='$id'";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Manager Updated!'); window.location.href='manage_property_manager.php';</script>";
    }
}

$result = mysqli_query($conn, 
    "SELECT * FROM users 
     WHERE role='manager' 
     ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Property Managers - Hira Rentals</title>
    <style>
        body{ font-family: Arial; background: #f5f5f5; margin: 0; }
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
        .add-form{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .add-form input{
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 180px;
        }
        .add-form button{
            padding: 8px 20px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        table{
            width: 100%;
            background: #fff;
            border-radius: 10px;
            border-collapse: collapse;
        }
        th{
            background: #E8622A;
            color: #fff;
            padding: 12px;
            text-align: left;
        }
        td{ padding: 12px; border-bottom: 1px solid #eee; }
        .btn-delete{
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-edit{
            background: #ffc107;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }
        .edit-form{
            display: none;
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .edit-form input{
            padding: 6px;
            margin: 3px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 150px;
        }
        .edit-form button{
            padding: 6px 15px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        .logout{
            background: #E8622A;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .no-data{ text-align: center; padding: 50px; color: #999; }
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
        <h2>Manage Property Managers</h2>

        <div class="add-form">
            <h3>Add New Property Manager</h3>
            <form method="POST">
                <input type="text" name="first_name" 
                       placeholder="First Name" required>
                <input type="text" name="last_name" 
                       placeholder="Last Name" required>
                <input type="email" name="email" 
                       placeholder="Email" required>
                <input type="text" name="phone" 
                       placeholder="Phone" required>
                <input type="password" name="password" 
                       placeholder="Password" required>
                <button type="submit" name="add_manager">
                    + Add Manager
                </button>
            </form>
        </div>

        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td>
                        <button class="btn-edit"
                            onclick="toggleEdit('edit-<?php echo $row['id']; ?>')">
                            Edit
                        </button>
                        <a href="?delete=<?php echo $row['id']; ?>"
                           onclick="return confirm('Delete this manager?')"
                           class="btn-delete"
                           style="text-decoration:none">
                            Delete
                        </a>

                        <div class="edit-form" 
                             id="edit-<?php echo $row['id']; ?>">
                            <form method="POST">
                                <input type="hidden" name="manager_id" 
                                       value="<?php echo $row['id']; ?>">
                                <input type="text" name="first_name" 
                                       value="<?php echo $row['first_name']; ?>" required>
                                <input type="text" name="last_name" 
                                       value="<?php echo $row['last_name']; ?>" required>
                                <input type="email" name="email" 
                                       value="<?php echo $row['email']; ?>" required>
                                <input type="text" name="phone" 
                                       value="<?php echo $row['phone']; ?>">
                                <button type="submit" name="update_manager">
                                    Update
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">
                        No property managers found!
                    </td>
                </tr>
            <?php endif; ?>
        </table>
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