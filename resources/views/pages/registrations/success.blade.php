<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .success-container {
            max-width: 600px;
            width: 100%;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .checkmark {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .checkmark-path {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: draw 0.5s ease-out 0.3s forwards;
        }

        @keyframes draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .success-message {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h5 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin-bottom: 8px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <svg class="checkmark" viewBox="0 0 52 52">
                    <path class="checkmark-path" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                </svg>
            </div>

            <h1>Registration Successful!</h1>
            <p class="success-message">
                Thank you for registering. Your registration has been submitted successfully.
            </p>

            <div class="info-box">
                <h5>What's Next?</h5>
                <ul>
                    <li>Your registration is being reviewed</li>
                    <li>You will receive a confirmation email shortly</li>
                    <li>Your certificate will be generated once approved</li>
                    <li>Keep an eye on your email for updates</li>
                </ul>
            </div>

            <p class="text-muted">
                <small>You may now close this window.</small>
            </p>
        </div>

        <div class="text-center mt-4">
            <p class="text-white mb-0">
                <small>&copy; {{ date('Y') }} Certificate Generator. All rights reserved.</small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
