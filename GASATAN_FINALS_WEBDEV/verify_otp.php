<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'config.php';

$email = '';
$otp = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $check_stmt = $conn->prepare("SELECT otp_code, otp_expire FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_otp = $row['otp_code'];
        $otp_expire = $row['otp_expire'];

        if ($otp === $stored_otp) {
            if (strtotime($otp_expire) >= time()) {
                $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
                $update_stmt->bind_param("s", $email);
                if ($update_stmt->execute()) {
                    $message = "✅ OTP verified successfully. You may now log in.";
                    
                } else {
                    $message = "❌ Failed to update verification status.";
                }
            } else {
                $message = "⚠️ OTP has expired. Please request a new one.";
            }
        } else {
            $message = "❌ OTP code mismatch. Please try again.";
        }
    } else {
        $message = "⚠️ No OTP found for this email. Please request a new OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('BG.jpg');
            background-size: cover;
            background-position: center;
            padding: 20px; /* Optional padding for better alignment */
        }

        .message-container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .message-container h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: #333;
        }

        .message-container p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 1rem;
        }

        .message-container .success {
            color: green;
        }

        .message-container .error {
            color: red;
        }

        .message-container .info {
            color: #ff9800;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h2>OTP Verification</h2>
        <p class="<?php
            if (strpos($message, '✅') !== false) echo 'success';
            elseif (strpos($message, '❌') !== false) echo 'error';
            else echo 'info';
        ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
</body>
</html>
