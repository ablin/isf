<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../config/config.inc.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(405);
    exit;
}

$headers = getallheaders();
if (!isset($headers["Authorization"]) || $headers["Authorization"] != "DZQ4V5hyne,TR.K56r_IL6J'he(Nfjy?KrysEHvQZGzku.") {
    http_response_code(401);
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));
$msg = json_encode([
    "value" => $data
]);

//Connexion au serveur RabbitMQ local
$rabbitConnection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$ch = $rabbitConnection->channel();

$ch->queue_declare(
    'file-d-attente', //nom de notre file d'attente
    false,            //passif
    true,             //durable
    false,            //exclusif
    false             //auto-suppression
);

$amqpMessage = new AMQPMessage($msg);

$ch->basic_publish($amqpMessage, '', 'file-d-attente');

$ch->close();
$rabbitConnection->close();

http_response_code(200);