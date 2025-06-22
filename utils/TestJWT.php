<?php

require_once '../lib/Firebase/JWT/Key.php';
require_once '../lib/Firebase/JWT/JWT.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = 'Test 123';

// Payload data (claims)
$payload = [
    'sub' => 'mzijlstra@gmail.com',         // Subject (e.g., user ID)
    'exp' => time() + 3600,        // Expiration time (1 hour from now)
];

try {
    // Encode the JWT
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    echo 'Generated JWT: '.$jwt."\n";

    // Optionally, decode to verify
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo 'Decoded Payload: '.print_r((array) $decoded, true)."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
