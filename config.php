<?php
// Load environment variables from .env file if you're using one
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Get API key from environment variable
define('API_KEY', getenv('OPENAI_API_KEY') ?: $_ENV['OPENAI_API_KEY'] ?? '');

// Ensure API key is set
if (empty(API_KEY)) {
    die('API key not configured');
}
