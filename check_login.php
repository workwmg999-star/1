<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['http_errors' => false]);
$baseUrl = 'http://127.0.0.1:8000/api/v1';

$res = $client->post("$baseUrl/auth/login", [
    'json' => [
        'email'    => 'owner@docuscan.test',
        'password' => 'password123',
    ],
]);

echo "Status: " . $res->getStatusCode() . "\n";
echo "Response: " . $res->getBody()->getContents() . "\n";
