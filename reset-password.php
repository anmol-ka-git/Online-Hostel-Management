<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f8f8f8;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input, button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Reset Password</h2>
        <p>Enter your new password</p>

        <?php
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            echo "<p style='color: red;'>Invalid or missing token!</p>";
            exit;
        }
        ?>

        <form action="update-password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>

</body>
</html>
