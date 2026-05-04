<?php
// Proxy reverso para o backend Node.js (porta 3000)
// URL: /dev/aibiorenew/api.php/auth/login -> http://127.0.0.1:3000/auth/login

$backend = 'http://127.0.0.1:3000';
$path    = $_SERVER['PATH_INFO'] ?? '/';
$query   = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
$url     = $backend . $path . $query;

$method  = $_SERVER['REQUEST_METHOD'];
$body    = file_get_contents('php://input');

$headers = [
    'Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'application/json'),
];
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $headers[] = 'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION'];
}

$ctx = stream_context_create([
    'http' => [
        'method'        => $method,
        'header'        => implode("\r\n", $headers),
        'content'       => $body,
        'ignore_errors' => true,
    ]
]);

$response = file_get_contents($url, false, $ctx);
$status   = $http_response_header[0] ?? 'HTTP/1.1 502 Bad Gateway';

// Repropagar código de estado
preg_match('/\d{3}/', $status, $m);
http_response_code((int)($m[0] ?? 502));

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($method === 'OPTIONS') { exit; }

echo $response;
