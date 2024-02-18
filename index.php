<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitizeInput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

$results = null;
$hasError = false;
$errorMsg = "";
$ipAddress = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["ipAddress"])) {
    $ipAddress = sanitizeInput($_POST["ipAddress"]);

    // Validate IP address
    if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
        $hasError = true;
        $errorMsg = "Please enter a valid IP address.";
    } else {
        // Initialize cURL
        $curl = curl_init();
        $url = "https://dedicated-servers.reliablesite.dev/v2/DDoS/GetDDoSAttacks?page=1&searchIP=" . $ipAddress;
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "accept: */*",
                "Authorization: Bearer YOUR_AUTHORIZATION_TOKEN_HERE" // Replace with your own authorization token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $hasError = true;
            $errorMsg = "Error while making the request: " . $err;
        } else {
            $results = json_decode($response, true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDoS Attack Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
            font-family: 'Roboto', sans-serif;
            color: #fff; 
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 50px; 
        }

        .search-box {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 45px;
            border-radius: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 4px solid rgba(255, 255, 255, 0.3);
            text-align: center;
            max-width: 650px; 
        }

        .btn-primary {
            background-color: #2575fc; 
            border: none;
            padding: 15px 30px; 
            font-size: 16px; 
            cursor: pointer;
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s, padding 0.3s; 
            color: #ffffff;
            margin-top: 20px; 
        }

        .btn-primary:hover {
            background-color: #5599ff; 
            transform: scale(1.01); 
        }

        .form-control {
            background-color: transparent;
            border: 3px solid #fff;
            color: #fff;
            border-radius: 15px;
            padding: 15px 25px; 
            text-align: center;
            transition: border-color 0.3s;
            margin-bottom: 20px; 
            color: #000; 
        }

        .form-control:focus {
            border-color: #5599ff;
            outline: none;
        }

        .error-message {
            color: #ff3860;
            margin-top: 10px;
        }

        .accordion-button {
            background-color: #fffff !important;
            border: none;
            padding: 10px 20px; 
            font-size: 16px; 
            cursor: pointer;
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s, padding 0.3s; 
            color: #000000;
            margin-top: 20px; 
        }

        .accordion-button:hover {
            background-color: #ffffff; 
            transform: scale(1.01); 
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-item {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            border: none;
        }

        .accordion-collapse {
            transition: max-height 0.3s ease-out, padding 0.3s; 
            display: none; 
        }

        .accordion-body {
            background-color: rgba(255, 255, 255, 0.8);
            color: #000;
            line-height: 1.5;
            padding: 15px; 
        }

        .logo {
            margin-bottom: 30px; /* Espacio debajo del logo */
            width: 200px; /* Ajuste de tamaño del logo */
        }

        ::placeholder {
            color: white;
        }
    </style>
</head>
<body>    <div class="container">
        <img src="YOUR_LOGO_URL_HERE" alt="Logo" class="logo"> 
        <div class="search-box">
            <h2 style="color: #fff;">DDoS Report Lookup</h2> 
            <form action="" method="POST">
                <div class="mb-3">
		<input type="text" class="form-control" id="ipAddress" name="ipAddress" placeholder="Enter IP address" required value="<?= htmlspecialchars($ipAddress) ?>" style="color: white;">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <?php if ($hasError): ?>
            <div class="error-message"><?= $errorMsg ?></div>
            <?php endif; ?>
            <?php if ($results && !$hasError && isset($results['data']) && is_array($results['data'])): ?>
            <div class="mt-4">
                <?php foreach ($results['data'] as $attack): 
                $startTime = new DateTime($attack['startTimeStamp']);
		$formattedStartTime = $startTime->format('m/d/Y \a\t H:i');
                ?>
                <div class="accordion" id="attackAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= htmlspecialchars($attack['attackId']) ?>">
                            <button class="accordion-button" type="button">
                                Attack on <?= $formattedStartTime ?>
                            </button>
                        </h2>
                        <div id="collapse<?= htmlspecialchars($attack['attackId']) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= htmlspecialchars($attack['attackId']) ?>" data-parent="#attackAccordion">
                            <div class="accordion-body">
                                <strong>Attack ID:</strong> <span class="attack-id"><?= htmlspecialchars($attack['attackId']) ?></span><br>
                                <strong>Target IP:</strong> <span class="target-ip"><?= htmlspecialchars($attack['targetIp']) ?></span><br>
                                <strong>Max PPS:</strong> <span class="max-pps"><?= htmlspecialchars($attack['maxPps']) ?> packets/s</span><br>
                                <strong>Max BPS:</strong> <span class="max-bps"><?= htmlspecialchars($attack['maxBps']) ?> bits/s</span><br>
                                <strong>Start Time:</strong> <span class="start-time"><?= $formattedStartTime ?></span><br>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.accordion-button').click(function() {
                $(this).closest('.accordion-item').find('.accordion-collapse').slideToggle(300); /* Animación más fluida */
            });
        });
    </script>
</body>
</html>
