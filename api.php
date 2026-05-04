<?php
// Proxy reverso para o backend Node.js (porta 3000)
// Usa cURL para preservar o Authorization header (Apache strip-o do $_SERVER)

$backend = 'http://127.0.0.1:3000';
$path    = $_SERVER['PATH_INFO'] ?? '/';
$query   = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
$url     = $backend . $path . $query;
$method  = $_SERVER['REQUEST_METHOD'];

// Responder a CORS preflight
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($method === 'OPTIONS') { http_response_code(204); exit; }

// Recolher todos os headers do request original
$allHeaders = function_exists('getallheaders') ? getallheaders() : [];

// Apache por vezes coloca o Authorization em REDIRECT_HTTP_AUTHORIZATION
$authHeader = $allHeaders['Authorization']
           ?? $allHeaders['authorization']
           ?? $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? null;

// Construir headers a enviar para o backend
$fwdHeaders = [
    'Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'application/json'),
];
if ($authHeader) {
    $fwdHeaders[] = 'Authorization: ' . $authHeader;
}

$body = file_get_contents('php://input');

// Executar request via cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $fwdHeaders,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_TIMEOUT        => 10,
]);

$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$hdrSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$response = substr($raw, $hdrSize);

http_response_code($httpCode ?: 502);
header('Content-Type: application/json; charset=utf-8');
echo $response;
