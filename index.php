<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; img-src 'self' https: data:; style-src 'self' 'unsafe-inline';");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'generate') {
        // Sanitize and validate input
        $prompt = isset($_POST['prompt']) ? trim(filter_var($_POST['prompt'], FILTER_SANITIZE_STRING)) : '';
        
        if (empty($prompt)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid prompt']);
            exit;
        }
        
        // Limit prompt length
        if (strlen($prompt) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Prompt too long']);
            exit;
        }
        
        // Call OpenAI API
        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . API_KEY,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo $response;
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Meme Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
        }
        
        button {
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:disabled {
            background-color: #cccccc;
        }
        
        .meme-container {
            position: relative;
            display: none;
        }
        
        .meme-container img {
            max-width: 100%;
            height: auto;
        }
        
        .meme-text {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            padding: 10px;
            background: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>AI Meme Generator</h1>
        
        <textarea id="prompt" placeholder="Describe the meme you want to generate..."></textarea>
        
        <button id="generateBtn" onclick="generateMeme()">Generate Meme</button>
        
        <div id="memeContainer" class="meme-container">
            <img id="generatedImage" alt="Generated meme">
            <input type="text" id="memeText" class="meme-text" placeholder="Add meme text...">
        </div>
    </div>

    <script>
        async function generateMeme() {
            const prompt = document.getElementById('prompt').value;
            const generateBtn = document.getElementById('generateBtn');
            const memeContainer = document.getElementById('memeContainer');
            const generatedImage = document.getElementById('generatedImage');
            
            if (!prompt) return;
            
            generateBtn.disabled = true;
            generateBtn.textContent = 'Generating...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'generate');
                formData.append('prompt', prompt);
                
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.data && data.data[0].url) {
                    generatedImage.src = data.data[0].url;
                    memeContainer.style.display = 'block';
                } else {
                    alert('Failed to generate image');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            } finally {
                generateBtn.disabled = false;
                generateBtn.textContent = 'Generate Meme';
            }
        }
    </script>
</body>
</html> 