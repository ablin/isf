<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('WWW-Authenticate: Basic realm="Test Authentication System"');
header('HTTP/1.0 401 Unauthorized');

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(405);
    exit ();
}

$headers = getallheaders();
if (!isset($headers["Authorization"]) || $headers["Authorization"] != "tralala") {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
var_dump($data);

echo json_encode(array("message" => "Import OK"));

http_response_code(200);
