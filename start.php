<?php

use Workerman\Worker;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

// Configure error logging for daemon mode
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/server.log');
ini_set('log_errors_max_len', '0');

// Set error reporting to capture all errors
error_reporting(E_ALL);

// Custom error handler to catch all error types
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $logMessage = sprintf(
        "[%s] PHP Error [%d]: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    error_log($logMessage, 3, __DIR__ . '/logs/server.log');
    return false; // Let PHP's internal handler also run
});

// Custom exception handler
set_exception_handler(function ($exception) {
    $logMessage = sprintf(
        "[%s] Uncaught Exception: %s in %s:%d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    error_log($logMessage, 3, __DIR__ . '/logs/server.log');

    // Also log to a separate error file for critical errors
    error_log($logMessage, 3, __DIR__ . '/logs/fatal_errors.log');

    // Exit with error code
    exit(1);
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logMessage = sprintf(
            "[%s] FATAL ERROR: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        error_log($logMessage, 3, __DIR__ . '/logs/server.log');
        error_log($logMessage, 3, __DIR__ . '/logs/fatal_errors.log');
    }
});

require_once __DIR__ . '/vendor/autoload.php';

// Load ModelFactory for converting client array data to Model objects
require_once __DIR__ . '/Models/ModelFactory.php';

// Load all game classes into memory ONCE at startup
// require_once __DIR__ . '/aifiles/LightsNET/SlotSettings.php';
// require_once __DIR__ . '/aifiles/LightsNET/Server.php';
// ... load other games ...

// Text protocol implies a newline "\n" at the end of every packet
$worker = new Worker("text://127.0.0.1:8787");
$worker->count = 4; // Run 4 processes

$worker->onMessage = function ($connection, $data) {
    try {
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

        // Convert array data to Model objects using ModelFactory
        // This enables games to use object methods like get_lines_percent_config()
        $settingsData = [
            'user' => \Models\ModelFactory::createUser($payload['user'] ?? []),
            'game' => \Models\ModelFactory::createGame($payload['game'] ?? []),
            'shop' => \Models\ModelFactory::createShop($payload['shop'] ?? []),
            'jpgs' => \Models\ModelFactory::createJPGs($payload['jpgs'] ?? []),
            'gameData' => $payload['gameData'] ?? [],
            'gameDataStatic' => $payload['gameDataStatic'] ?? [],
            'bankerService' => $payload['bankerService'] ?? null,
            'betLogs' => $payload['betLogs'] ?? [],
            'slotId' => $payload['slotId'] ?? '',
            'playerId' => $payload['playerId'] ?? null,
            'balance' => $payload['balance'] ?? 0,
            'jackpots' => $payload['jackpots'] ?? [],
            'state' => $state,
            'reelStrips' => $payload['reelStrips'] ?? []
        ];
        // error_log('Array data: ' . json_encode($settingsData, JSON_PRETTY_PRINT));
        // Instantiate logic (Fast because classes are pre-loaded)
        $slotSettings = new $settingsClass($settingsData);
        $server = new $serverClass();

        // Calculate
        $responseJson = $server->get($postData, $slotSettings);

        // Send back to TypeScript
        $connection->send($responseJson);
    } catch (Exception $e) {
        $errorLog = sprintf(
            "[%s] Worker Error: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        error_log($errorLog, 3, __DIR__ . '/logs/server.log');
        error_log($errorLog, 3, __DIR__ . '/logs/worker_errors.log');

        // Send error response back to client
        $connection->send(json_encode(['error' => 'Internal server error']));
    } catch (Error $e) {
        $errorLog = sprintf(
            "[%s] Worker Fatal Error: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        error_log($errorLog, 3, __DIR__ . '/logs/server.log');
        error_log($errorLog, 3, __DIR__ . '/logs/worker_errors.log');

        // Send error response back to client
        $connection->send(json_encode(['error' => 'Internal server error']));
    }
};

// Add worker error handling
$worker->onError = function ($connection, $code, $msg) {
    $errorLog = sprintf(
        "[%s] Worker Connection Error: Code %d - %s\n",
        date('Y-m-d H:i:s'),
        $code,
        $msg
    );
    error_log($errorLog, 3, __DIR__ . '/logs/server.log');
};

$worker->onWorkerError = function ($worker, $pid, $exit_code, $signal) {
    $errorLog = sprintf(
        "[%s] Worker Process Error: Worker %s died with PID %d, exit code %d, signal %s\n",
        date('Y-m-d H:i:s'),
        $worker->id ?? 'unknown',
        $pid,
        $exit_code,
        $signal
    );
    error_log($errorLog, 3, __DIR__ . '/logs/server.log');
    error_log($errorLog, 3, __DIR__ . '/logs/worker_process_errors.log');
};

Worker::runAll();
