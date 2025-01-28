<?php
// Function to remove boundary markers and extract key-value pairs from form data
function parseFormData($data) {
    $entries = [];

    // Remove boundary markers from the raw data
    $cleanedData = preg_replace('/--[\w\d-]+\s+/', '', $data);
    $cleanedData = trim($cleanedData); // Remove any leading/trailing whitespace

    // Split the data by line breaks
    $lines = array_filter(array_map('trim', explode("\n", $cleanedData)), function($line) {
        return strlen($line) > 0;
    });

    $fieldName = '';
    $fieldValue = '';
    $isName = true; // Flag to detect whether we are reading the field name or the value

    foreach ($lines as $line) {
        if (strpos($line, 'Content-Disposition: form-data; name="') === 0) {
            // Extract the field name
            preg_match('/name="([^"]+)"/', $line, $matches);
            $fieldName = $matches[1];
            $isName = false; // Now we expect the value in the next line
        } elseif (!$isName) {
            // The next line after the field name should be the field value
            $fieldValue = trim($line);
            $entries[$fieldName] = $fieldValue;
            $isName = true; // Reset to look for the next field name
        }
    }

    return $entries;
}

// Function to URL-encode form data
function encodeFormData($data) {
    $encodedData = [];
    foreach ($data as $key => $value) {
        $encodedData[] = urlencode($key) . '=' . urlencode($value);
    }
    return implode('&', $encodedData);
}

// Check if form is submitted
$urlEncodedData = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw form data from the textarea
    $rawData = $_POST['formData'];

    // Parse the form data
    $formData = parseFormData($rawData);

    // Encode the form data
    $urlEncodedData = encodeFormData($formData);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Data Parser</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1c1c1c;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        label {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #ccc;
        }

        textarea {
            width: 80%;
            height: 200px;
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #ddd;
            resize: none;
            font-size: 1rem;
            color: #fff;
            background-color: #333;
            position: relative;
            animation: lightning-strike 1s infinite alternate;
            display: block;
            margin: 0 auto;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .encoded-data {
            background-color: #333;
            border: 2px solid #4CAF50;
            padding: 20px;
            margin-top: 30px;
            border-radius: 10px;
            width: 80%;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            animation: lightning-strike 1s infinite alternate, popup 0.3s ease-out forwards;
            color: #fff;
            display: none;
            text-align: center; /* Center the content inside the popup */
        }

        .encoded-data h2 {
            color: #4CAF50;
            font-size: 1.8rem;
            margin-bottom: 15px;
            text-align: center;
        }

        pre {
            background-color: #222;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            word-wrap: break-word;
            white-space: pre-wrap;
            color: #fff;
            text-align: center; /* Center the encoded form data */
        }

        /* Lightning Strike Animation */
        @keyframes lightning-strike {
            0% {
                box-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #fff;
            }
            25% {
                box-shadow: 0 0 15px #0ff, 0 0 30px #0ff, 0 0 50px #0ff;
            }
            50% {
                box-shadow: 0 0 20px #0ff, 0 0 40px #0ff, 0 0 60px #0ff;
            }
            75% {
                box-shadow: 0 0 30px #0ff, 0 0 50px #0ff, 0 0 80px #0ff;
            }
            100% {
                box-shadow: 0 0 40px #fff, 0 0 70px #fff, 0 0 100px #fff;
            }
        }

        /* Faster 3D Popup Effect */
        @keyframes popup {
            0% {
                transform: scale(0.5);
                opacity: 0;
                box-shadow: 0 0 20px rgba(0, 255, 255, 0.7), 0 0 40px rgba(0, 255, 255, 0.7);
            }
            100% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 0 20px rgba(0, 255, 255, 0.7), 0 0 40px rgba(0, 255, 255, 0.7);
            }
        }

        /* Footer with moving text */
        .footer {
            margin-top: 50px;
            font-size: 1rem;
            color: #777;
            position: relative;
            width: 30%;
            text-align: center;
            overflow: hidden;
            padding: 10px 0;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .created-by {
            margin-bottom: 5px;
            font-size: 1.5rem;
            color: #4CAF50;
            animation: glowingText 2s infinite alternate;
            font-weight: bold;
        }

        .moving-text {
            position: absolute;
            white-space: nowrap;
            animation: moveText 5s linear infinite;
            font-size: 1.5rem;
            color: #4CAF50;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        @keyframes glowingText {
            0% {
                color: #4CAF50;
                text-shadow: 0 0 5px #4CAF50, 0 0 10px #4CAF50, 0 0 20px #4CAF50;
            }
            25% {
                color: #FF5733;
                text-shadow: 0 0 5px #FF5733, 0 0 10px #FF5733, 0 0 20px #FF5733;
            }
            50% {
                color: #FFD700;
                text-shadow: 0 0 5px #FFD700, 0 0 10px #FFD700, 0 0 20px #FFD700;
            }
            75% {
                color: #FF1493;
                text-shadow: 0 0 5px #FF1493, 0 0 10px #FF1493, 0 0 20px #FF1493;
            }
            100% {
                color: #4CAF50;
                text-shadow: 0 0 5px #4CAF50, 0 0 10px #4CAF50, 0 0 20px #4CAF50;
            }
        }

        @keyframes moveText {
            0% {
                left: 100%;
            }
            50% {
                left: 50%;
                transform: translateX(-50%);
            }
            100% {
                left: -100%;
            }
        }
    </style>
</head>
<body>

    <h1>Form Data Parser</h1>

    <form method="POST" action="">
        <label for="formData">Paste your raw form data (including boundaries):</label><br><br>
        <textarea name="formData" id="formData" placeholder="Paste your raw form data here..."></textarea><br><br>
        <input type="submit" value="Parse Data">
    </form>

    <?php if (!empty($urlEncodedData)): ?>
        <div class="encoded-data" id="popup">
            <h2>Encoded Form Data:</h2>
            <pre><?php echo htmlspecialchars($urlEncodedData); ?></pre>
        </div>
    <?php endif; ?>

    <!-- Footer with animations -->
    <div class="footer">
        <div class="footer-content">
            <span class="created-by">Created by: X I N</span>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <span class="moving-text">Telegram: https://t.me/posxinxin</span>
        </div>
    </div>

    <script>
        // Display the 3D popup message when encoded data exists
        if (document.getElementById("popup")) {
            document.getElementById("popup").style.display = 'block';
        }
    </script>
</body>
</html>
