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

// Delete User
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('User Deleted!'); window.location.href='manage_users.php';</script>";
}

// Add User
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users 
              (first_name, last_name, email, phone, role, password) 
              VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $first_name, $last_name, $email, $phone, $role, $password);

    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('User Added!'); window.location.href='manage_users.php';</script>";
    } else {
        echo "<script>alert('Email already exists!');</script>";
    }
}

// Update User
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])){
    $id = intval($_POST['user_id']);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "UPDATE users 
              SET first_name=?, last_name=?, email=?, phone=?, role=?
              WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone, $role, $id);

    if(mysqli_stmt_execute($stmt)){
        echo "<script>alert('User Updated!'); window.location.href='manage_users.php';</script>";
    }
}

// Fetch All Users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Hira Rentals</title>
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
        .navbar h2{
            color: #E8622A;
            margin: 0;
        }
        .container{
            padding: 30px;
        }
        .add-form{
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .add-form input,
        .add-form select{
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
        td{
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
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
        .edit-form input,
        .edit-form select{
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
        .role-badge{
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .role-admin{ background: #f8d7da; color: #721c24; }
        .role-manager{ background: #d4edda; color: #155724; }
        .role-owner{ background: #fff3cd; color: #856404; }
        .role-tenant{ background: #d1ecf1; color: #0c5460; }
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
        .no-data{
            text-align: center;
            padding: 50px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Hira Rentals</h2>
        <div>
            <a href="dashboard.php" class="btn-back">
                ← Back to Dashboard
            </a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Manage Users</h2>

        <!-- Add User Form -->
        <div class="add-form">
            <h3>Add New User</h3>
            <form method="POST">
                <input type="text" name="first_name" 
                       placeholder="First Name" required>
                <input type="text" name="last_name" 
                       placeholder="Last Name" required>
                <input type="email" name="email" 
                       placeholder="Email" required>
                <input type="text" name="phone" 
                       placeholder="Phone" required>
                <select name="role">
                    <option value="tenant">Tenant</option>
                    <option value="owner">Owner</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
                <input type="password" name="password" 
                       placeholder="Password" required>
                <button type="submit" name="add_user">
                    + Add User
                </button>
            </form>
        </div>

        <!-- Users Table -->
        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Action</th>
            </tr>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo htmlspecialchars($row['role']); ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-edit"
                            onclick="document.getElementById('edit-<?php echo $row['id']; ?>').style.display='block'">
                            Edit
                        </button>
                        <a href="?delete=<?php echo $row['id']; ?>"
                           onclick="return confirm('Delete this user?')"
                           class="btn-delete"
                           style="text-decoration:none">
                            Delete
                        </a>

                        <!-- Edit Form -->
                        <div class="edit-form" 
                             id="edit-<?php echo $row['id']; ?>">
                            <form method="POST">
                                <input type="hidden" name="user_id" 
                                       value="<?php echo $row['id']; ?>">
                                <input type="text" name="first_name" 
                                       value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
                                <input type="text" name="last_name" 
                                       value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
                                <input type="email" name="email" 
                                       value="<?php echo htmlspecialchars($row['email']); ?>" required>
                                <input type="text" name="phone" 
                                       value="<?php echo htmlspecialchars($row['phone']); ?>">
                                <select name="role">
                                    <option value="tenant" <?php if($row['role']=='tenant') echo 'selected'; ?>>Tenant</option>
                                    <option value="owner" <?php if($row['role']=='owner') echo 'selected'; ?>>Owner</option>
                                    <option value="manager" <?php if($row['role']=='manager') echo 'selected'; ?>>Manager</option>
                                    <option value="admin" <?php if($row['role']=='admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_user">
                                    Update
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">
                        No users found!
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
