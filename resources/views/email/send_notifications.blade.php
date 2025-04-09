<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f4f8;
            margin: 0;
            padding: 0;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4A90E2;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .email-body {
            padding: 30px 20px;
        }

        .email-body h2 {
            color: #333;
            margin-top: 0;
        }

        .email-body p {
            color: #555;
            font-size: 16px;
            line-height: 1.5;
        }

        .otp-code {
            margin: 20px 0;
            background-color: #EAF4FF;
            color: #1a1a1a;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 12px 24px;
            border-radius: 8px;
            text-align: center;
            display: inline-block;
        }

        .email-footer {
            text-align: center;
            background-color: #f1f1f1;
            color: #888;
            font-size: 13px;
            padding: 15px 10px;
        }
    </style>
</head>
<body>

    <div class="email-wrapper">
        <div class="email-header">
            <h1>Verify Your Identity</h1>
        </div>
        <div class="email-body">
            <h2>Hello Lovepreet,</h2>
            <p>Thank you for using our services. To continue, please use the following One-Time Password (OTP):</p>
            <div class="otp-code">12345</div>
            <p>This OTP is valid for 10 minutes. If you did not request this, please ignore this message.</p>
        </div>
        <div class="email-footer">
            &copy; 2025 Your Company Name. All rights reserved.
        </div>
    </div>

</body>
</html>
