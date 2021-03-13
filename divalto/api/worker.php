<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/import.class.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

//Connexion au serveur RabbitMQ local
$rabbitConnection = new AMQPStreamConnection('rabbitmq.interface-web.fr', 5672, 'interface-web', 'hRiIiamjxbWjhNdtNFkqirUojWLCimAL');
//$rabbitConnection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$ch = $rabbitConnection->channel();

$ch->queue_declare(
    'interservicefreins', //nom de notre file d'attente
    false,            //passif
    true,             //durable
    false,            //exclusif
    false             //auto-suppression
);

echo "Waiting...";

$callback = function($msg) {
    $msgContent = json_decode($msg->body, false);
    echo "Received value: ";
    print_r($msgContent->value);
    echo "\n";

    $import = new Import($msgContent->value);

    // On envoi finalement le signal de retour
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// On spécifie qu'un worker ne peut traiter qu'un message à la fois
$ch->basic_qos(null, 1, null);

$ch->basic_consume(
    'interservicefreins', //nom de notre file d'attente
    '',               //worker-tag
    false,            //n'envoi pas le message à la connexion qui l'a émis
    false,            //le serveur ne s'attendra pas à un signal de retour
    false,            //exclusif (limite la file d'attente à ce worker)
    false,            //le client ne s'attendra pas à une réponse du serveur
    $callback         //fonction de traitement appelé à la réception d'un message
);

while (count($ch->callbacks)) {
    $ch->wait();
}

$ch->close();
$rabbitConnection->close();
