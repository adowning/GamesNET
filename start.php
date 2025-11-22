<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// Load all game classes into memory ONCE at startup
// require_once __DIR__ . '/aifiles/LightsNET/SlotSettings.php';
// require_once __DIR__ . '/aifiles/LightsNET/Server.php';
// ... load other games ...

// Text protocol implies a newline "\n" at the end of every packet
$worker = new Worker("text://127.0.0.1:8787");
$worker->count = 4; // Run 4 processes

$worker->onMessage = function ($connection, $data) {
    // $data is the JSON string sent from TypeScript
    $payload = json_decode($data, true);
    // echo "Received Payload: " . print_r($payload->game, true) . "\n";
    // echo "Received Payload: " . $payload->postData->action . "\n";
    $gameId = $payload['gameId'];

    $postData = $payload['postData'];
    $state = $payload['state'];

    // Route to the correct game class based on $gameId
    // (You can use a simple factory pattern here)
    $namespace = "Games\\{$gameId}\\";
    $serverClass = $namespace . "Server";
    $settingsClass = $namespace . "SlotSettings";

    // Instantiate logic (Fast because classes are pre-loaded)
    $slotSettings = new $settingsClass($payload);
    $server = new $serverClass();

    // Calculate
    $responseJson = $server->get($postData, $slotSettings);

    // Send back to TypeScript
    $connection->send($responseJson);
};

Worker::runAll();
