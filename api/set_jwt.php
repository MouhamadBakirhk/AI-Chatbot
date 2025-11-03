<?php
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die(".env file not found!\n");
}

 
$key = 'base64:' . base64_encode(random_bytes(32));

// اقرأ محتوى .env
$env = file_get_contents($envFile);

// إذا موجود JWT_SECRET عدله، إذا مش موجود ضيفه
if (strpos($env, 'JWT_SECRET=') !== false) {
    $env = preg_replace('/JWT_SECRET=.*/', 'JWT_SECRET=' . $key, $env);
} else {
    $env .= "\nJWT_SECRET=" . $key;
}

 
file_put_contents($envFile, $env);

echo "JWT_SECRET has been set to: $key\n";
