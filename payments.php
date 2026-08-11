<?php
session_start();
include 'config/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$merchant_id = '10000100';
$merchant_key = '46f0cd694581a';
$cancel_url = 'http://localhost/hira_property/dashboard.php';
$return_url = 'http://localhost/hira_property/payment_success.php';

$amount = isset($_GET['amount']) ? $_GET['amount'] : '100.00';
$property_id = isset($_GET['property_id']) ? $_GET['property_id'] : 1;
$_SESSION['property_id'] = $property_id;

$item_name = 'Hira Property Rent';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connecting to PayFast...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .loading-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 400px;
            border: 1px solid #eef2f5;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #E8622A;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #2d3748; margin-bottom: 10px; font-size: 20px; }
        p { color: #718096; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
        .amount-info{
            background: #fff5f0;
            border: 1px solid #E8622A;
            border-radius: 8px;
            padding: 10px 20px;
            margin-bottom: 15px;
            font-weight: bold;
            color: #E8622A;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="loading-card">
        <div class="spinner"></div>
        <h2>Connecting to PayFast Secure Gateway</h2>
        <div class="amount-info">
            Amount: PKR <?php echo number_format($amount); ?>
        </div>
        <p>Please wait while we redirect you to the secure payment 
           page. Do not close or refresh this window.</p>
    </div>

    <form id="payfast_form" 
          action="https://sandbox.payfast.co.za/eng/process" 
          method="POST">
        <input type="hidden" name="merchant_id" 
               value="<?php echo $merchant_id; ?>">
        <input type="hidden" name="merchant_key" 
               value="<?php echo $merchant_key; ?>">
        <input type="hidden" name="return_url" 
               value="<?php echo $return_url; ?>">
        <input type="hidden" name="cancel_url" 
               value="<?php echo $cancel_url; ?>">
        <input type="hidden" name="amount" 
               value="<?php echo $amount; ?>">
        <input type="hidden" name="item_name" 
               value="<?php echo $item_name; ?>">
    </form>

    <script>
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('payfast_form').submit();
            }, 100);
        };
    </script>
</body>
</html>