<?php
session_start();
include 'config/db_connection.php';
include 'config/mail_config.php';

if(!isset($_SESSION['pending_verification_email'])){
    header("Location: register.php");
    exit();
}

$email = $_SESSION['pending_verification_email'];
$error = "";
$success = "";

// ---- Resend OTP ----
if(isset($_POST['resend_otp'])){
    $new_otp = rand(100000, 999999);
    $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    mysqli_query($conn, 
        "UPDATE users SET otp_code='$new_otp', otp_expiry='$new_expiry' 
         WHERE email='$email'");

    if(send_otp_email($email, $new_otp)){
        $success = "Naya OTP bhej diya gaya hai, apna inbox check karein.";
    } else {
        $error = "Email bhejne mein masla hua. Thori dair baad dobara try karein.";
    }
}

// ---- Verify OTP ----
if(isset($_POST['verify_otp'])){
    $entered_otp = $_POST['otp'];

    $result = mysqli_query($conn, 
        "SELECT otp_code, otp_expiry FROM users WHERE email='$email'");
    $row = mysqli_fetch_assoc($result);

    if(!$row){
        $error = "Account nahi mila. Dobara register karein.";
    } elseif($row['otp_code'] != $entered_otp){
        $error = "Galat OTP! Dobara check karke likhein.";
    } elseif(strtotime($row['otp_expiry']) < time()){
        $error = "OTP expire ho chuka hai. 'Resend OTP' button dabayein.";
    } else {
        mysqli_query($conn, 
            "UPDATE users SET is_verified=1, otp_code=NULL, otp_expiry=NULL 
             WHERE email='$email'");
        
        unset($_SESSION['pending_verification_email']);

        echo "<script>
            alert('Email verify ho gayi! Ab aap login kar sakte hain.');
            window.location='login.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Email - Hira Rentals</title>
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
            padding: 35px 30px;
            border-radius: 10px;
            border: 1px solid #ddd;
            width: 380px;
            text-align: center;
        }
        h2{ margin-bottom: 8px; color: #333; }
        p.sub{ color: #777; font-size: 14px; margin-bottom: 22px; }
        p.sub b{ color: #E8622A; }
        input{
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            text-align: center;
            font-size: 20px;
            letter-spacing: 6px;
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
        .resend-btn{
            background: #fff;
            color: #E8622A;
            border: 1px solid #E8622A;
        }
        .msg{
            padding: 10px;
            border-radius: 6px;
            font-size: 13.5px;
            margin-bottom: 15px;
        }
        .error{ background: #fbe9e9; color: #c0392b; }
        .success{ background: #e9f9ef; color: #1e7e42; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>📧 Verify Your Email</h2>
        <p class="sub">Humne ek 6-digit code bheja hai <b><?php echo htmlspecialchars($email); ?></b> par</p>

        <?php if($error): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="------" 
                   maxlength="6" pattern="\d{6}" required autofocus>
            <button type="submit" name="verify_otp">Verify Code</button>
        </form>

        <form method="POST">
            <button type="submit" name="resend_otp" class="resend-btn">
                Resend OTP
            </button>
        </form>
    </div>
</body>
</html>