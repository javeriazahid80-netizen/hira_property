<?php
include 'config/db_connection.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password_raw = $_POST['password'];

    if (!preg_match('/[A-Z]/', $password_raw) || 
        !preg_match('/[a-z]/', $password_raw) || 
        !preg_match('/[0-9]/', $password_raw) || 
        strlen($password_raw) < 8) {
        echo "<script>alert('Password weak hai! Kam se kam 8 characters, ek bara letter aur ek number shamil karein.'); window.history.back();</script>";
        exit();
    }

    if($role != 'tenant' && $role != 'owner'){
        echo "<script>alert('Invalid role selected!'); window.history.back();</script>";
        exit();
    }

    $check_email = mysqli_query($conn, 
        "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check_email) > 0){
        echo "<script>alert('This email is already registered! Please use a different email or login.'); window.history.back();</script>";
        exit();
    }

    $password = md5($password_raw);

    $query = "INSERT INTO users 
              (first_name, last_name, email, phone, role, password) 
              VALUES 
              ('$first_name','$last_name','$email','$phone','$role','$password')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>
            alert('Registration Successful!');
            window.location='login.php';
        </script>";
    } else {
        echo "<script>alert('Registration failed! Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Hira Rentals</title>
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
        .form-box{
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #ddd;
            width: 400px;
        }
        h2{ text-align: center; margin-bottom: 20px; }
        .row{ display: flex; gap: 10px; }
        input, select{
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button{
            width: 100%;
            padding: 12px;
            background: #E8622A;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:disabled{
            background: #ccc;
            cursor: not-allowed;
        }
        p{ text-align: center; margin-top: 15px; }
        a{ color: #E8622A; }
        .password-wrapper{ position: relative; }
        .password-wrapper input{ padding-right: 40px; }
        .eye-icon{
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Create Account</h2>
        <form method="POST">
            <div class="row">
                <input type="text" name="first_name" 
                       placeholder="First Name" required>
                <input type="text" name="last_name" 
                       placeholder="Last Name" required>
            </div>
            <input type="email" name="email" 
                   placeholder="your@email.com" required>
            <input type="text" name="phone" 
                   placeholder="+92 300 0000000" required>
            <select name="role">
                <option value="tenant">Tenant</option>
                <option value="owner">Owner</option>
            </select>

            <div class="password-wrapper">
                <input type="password" id="password" name="password" 
                       placeholder="Create a strong password" 
                       required onkeyup="checkPasswordStrength();">
                <span class="eye-icon" 
                      onclick="togglePassword('password')">
                    👁️
                </span>
            </div>

            <span id="password-err" 
                  style="color:red; font-size:13px; display:block; 
                         margin-top:2px; margin-bottom:8px; 
                         font-weight:bold;">
            </span>

            <button type="submit" id="register-btn">Register</button>
        </form>
        <p>Already have account? 
           <a href="login.php">Log In</a>
        </p>
    </div>

    <script>
    function togglePassword(id){
        var input = document.getElementById(id);
        if(input.type === 'password'){
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }

    function checkPasswordStrength(){
        var password = document.getElementById('password').value;
        var errorSpan = document.getElementById('password-err');
        var submitBtn = document.getElementById('register-btn');
        var strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

        if(password.length === 0){
            errorSpan.innerHTML = "";
            submitBtn.disabled = false;
            return;
        }

        if(!strongRegex.test(password)){
            errorSpan.style.color = "red";
            errorSpan.innerHTML = "❌ Password must be at least 8 characters, include 1 uppercase, 1 lowercase, and 1 number.";
            submitBtn.disabled = true;
        } else {
            errorSpan.style.color = "green";
            errorSpan.innerHTML = "✅ Strong Password!";
            submitBtn.disabled = false;
        }
    }
    </script>
</body>
</html>