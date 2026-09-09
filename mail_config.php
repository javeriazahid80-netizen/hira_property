<?php
// ============================================
// Hira Property - Email (OTP) Sending Config
// ============================================
// Yeh file Gmail ke SMTP server se seedha baat karti hai
// (koi external library/PHPMailer download karne ki zaroorat nahi)

// ---- Apni Gmail details (already set) ----
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'zunaisha5677@gmail.com');
define('SMTP_PASSWORD', 'oikxvabeqqajnexw'); // App Password (spaces ke bina)
define('SMTP_FROM_EMAIL', 'zunaisha5677@gmail.com');
define('SMTP_FROM_NAME', 'Hira Property');

// Helper: SMTP server se ek response line parhna
function smtp_read_response($socket){
    $data = "";
    while($str = fgets($socket, 515)){
        $data .= $str;
        if(substr($str, 3, 1) == " ") break;
    }
    return $data;
}

// Main function: OTP email bhejta hai
// Return: true (kamyabi) ya false (nakami)
function send_otp_email($to_email, $otp_code){
    $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
    if(!$socket){
        error_log("SMTP connection failed: $errstr ($errno)");
        return false;
    }

    smtp_read_response($socket);

    fputs($socket, "EHLO localhost\r\n");
    smtp_read_response($socket);

    fputs($socket, "STARTTLS\r\n");
    smtp_read_response($socket);

    if(!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)){
        fclose($socket);
        return false;
    }

    fputs($socket, "EHLO localhost\r\n");
    smtp_read_response($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    smtp_read_response($socket);

    fputs($socket, base64_encode(SMTP_USERNAME) . "\r\n");
    smtp_read_response($socket);

    fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
    $auth_response = smtp_read_response($socket);

    if(substr($auth_response, 0, 3) != "235"){
        error_log("SMTP auth failed: $auth_response");
        fclose($socket);
        return false;
    }

    fputs($socket, "MAIL FROM: <" . SMTP_FROM_EMAIL . ">\r\n");
    smtp_read_response($socket);

    fputs($socket, "RCPT TO: <" . $to_email . ">\r\n");
    smtp_read_response($socket);

    fputs($socket, "DATA\r\n");
    smtp_read_response($socket);

    $subject = "Your Hira Property Verification Code";
    $body  = "Assalam-o-Alaikum,\r\n\r\n";
    $body .= "Your verification code (OTP) is: $otp_code\r\n\r\n";
    $body .= "This code will expire in 10 minutes.\r\n";
    $body .= "If you did not request this, please ignore this email.\r\n\r\n";
    $body .= "- Hira Property Management Services";

    $message  = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $message .= "To: <" . $to_email . ">\r\n";
    $message .= "Subject: $subject\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "\r\n" . $body . "\r\n.\r\n";

    fputs($socket, $message);
    $send_response = smtp_read_response($socket);

    fputs($socket, "QUIT\r\n");
    smtp_read_response($socket);
    fclose($socket);

    // "250" ka matlab hai email successfully accept hui
    return (strpos($send_response, "250") !== false);
}

// General purpose email sender (contact form ke liye)
// Reply-To wale sender ka email set karta hai taake reply seedha unhe jaye
function send_contact_email($visitor_name, $visitor_email, $subject_line, $message_body){
    $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
    if(!$socket){
        error_log("SMTP connection failed: $errstr ($errno)");
        return false;
    }

    smtp_read_response($socket);
    fputs($socket, "EHLO localhost\r\n");
    smtp_read_response($socket);
    fputs($socket, "STARTTLS\r\n");
    smtp_read_response($socket);

    if(!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)){
        fclose($socket);
        return false;
    }

    fputs($socket, "EHLO localhost\r\n");
    smtp_read_response($socket);
    fputs($socket, "AUTH LOGIN\r\n");
    smtp_read_response($socket);
    fputs($socket, base64_encode(SMTP_USERNAME) . "\r\n");
    smtp_read_response($socket);
    fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
    $auth_response = smtp_read_response($socket);

    if(substr($auth_response, 0, 3) != "235"){
        fclose($socket);
        return false;
    }

    $to_email = SMTP_FROM_EMAIL; // Hira Property ki team ko yeh mail milegi

    fputs($socket, "MAIL FROM: <" . SMTP_FROM_EMAIL . ">\r\n");
    smtp_read_response($socket);
    fputs($socket, "RCPT TO: <" . $to_email . ">\r\n");
    smtp_read_response($socket);
    fputs($socket, "DATA\r\n");
    smtp_read_response($socket);

    $subject = "Website Contact: " . $subject_line;
    $body  = "New message from Hira Property website contact form:\r\n\r\n";
    $body .= "Name: $visitor_name\r\n";
    $body .= "Email: $visitor_email\r\n";
    $body .= "Subject: $subject_line\r\n\r\n";
    $body .= "Message:\r\n$message_body\r\n";

    $message  = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $message .= "Reply-To: $visitor_name <$visitor_email>\r\n";
    $message .= "To: <" . $to_email . ">\r\n";
    $message .= "Subject: $subject\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "\r\n" . $body . "\r\n.\r\n";

    fputs($socket, $message);
    $send_response = smtp_read_response($socket);
    fputs($socket, "QUIT\r\n");
    smtp_read_response($socket);
    fclose($socket);

    return (strpos($send_response, "250") !== false);
}
?>
