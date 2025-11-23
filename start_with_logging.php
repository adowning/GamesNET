<?php

/**
 * Enhanced startup script with comprehensive error logging
 * Use this script instead of start.php for better error capture in daemon mode
 */

use Workerman\Worker;

// Define log file paths
define('ERROR_LOG', __DIR__ . '/logs/server.log');
define('FATAL_ERROR_LOG', __DIR__ . '/logs/fatal_errors.log');
define('WORKER_ERROR_LOG', __DIR__ . '/logs/worker_errors.log');
define('WORKER_PROCESS_LOG', __DIR__ . '/logs/worker_process_errors.log');

// Initialize log files
touch(ERROR_LOG);
touch(FATAL_ERROR_LOG);
touch(WORKER_ERROR_LOG);
touch(WORKER_PROCESS_LOG);

// Configure PHP error handling for daemon mode
function configureErrorHandling()
{
    // Disable display but enable logging
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ERROR_LOG);
    ini_set('log_errors_max_len', '0');

    // Capture all errors
    error_reporting(E_ALL);

    // Custom error handler - catch all error types
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        $timestamp = date('Y-m-d H:i:s');
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        $errorType = $errorTypes[$errno] ?? "UNKNOWN_ERROR_$errno";
        $logMessage = "[$timestamp] $errorType: $errstr in $errfile on line $errline\n";

        error_log($logMessage, 3, ERROR_LOG);

        // For critical errors, also log to separate file
        if (in_array($errno, [E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            error_log($logMessage, 3, FATAL_ERROR_LOG);
        }

        return false; // Allow PHP's default handler to also run
    });

    // Custom exception handler - catch all uncaught exceptions
    set_exception_handler(function ($exception) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] UNCAUGHT_EXCEPTION: " . $exception->getMessage() .
            " in " . $exception->getFile() . ":" . $exception->getLine() .
            "\nStack trace:\n" . $exception->getTraceAsString() . "\n";

        error_log($logMessage, 3, ERROR_LOG);
        error_log($logMessage, 3, FATAL_ERROR_LOG);

        // Exit gracefully
        exit(1);
    });

    // Shutdown function - catches fatal errors and parse errors
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR
        ])) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] FATAL_SHUTDOWN: " . $error['message'] .
                " in " . $error['file'] . ":" . $error['line'] . "\n";

            error_log($logMessage, 3, ERROR_LOG);
            error_log($logMessage, 3, FATAL_ERROR_LOG);
        }
    });
}

// Log startup
function logStartup()
{
    $timestamp = date('Y-m-d H:i:s');
    $message = "\n=== SERVER STARTUP ===\n";
    $message .= "Timestamp: $timestamp\n";
    $message .= "PHP Version: " . PHP_VERSION . "\n";
    $message .= "Workerman Version: " . Worker::VERSION . "\n";
    $message .= "Log files configured:\n";
    $message .= "- Main error log: " . ERROR_LOG . "\n";
    $message .= "- Fatal errors: " . FATAL_ERROR_LOG . "\n";
    $message .= "- Worker errors: " . WORKER_ERROR_LOG . "\n";
    $message .= "- Worker process errors: " . WORKER_PROCESS_LOG . "\n";
    $message .= "======================\n\n";

    error_log($message, 3, ERROR_LOG);
}

// Initialize error handling
configureErrorHandling();
logStartup();

require_once __DIR__ . '/vendor/autoload.php';

// Load ModelFactory for converting client array data to Model objects
require_once __DIR__ . '/Models/ModelFactory.php';

// Text protocol implies a newline "\n" at the end of every packet
$worker = new Worker("text://127.0.0.1:8787");
$worker->count = 4; // Run 4 processes

$worker->onMessage = function ($connection, $data) {
    try {
        // Log incoming request (optional, for debugging)
        $timestamp = date('Y-m-d H:i:s');

        // $data is the JSON string sent from TypeScript
        $payload = json_decode($data, true);

        if (!$payload) {
            throw new Exception("Invalid JSON payload received");
        }

        // Extract required fields
        $gameId = $payload['gameId'] ?? null;
        $postData = $payload['postData'] ?? null;
        $state = $payload['state'] ?? null;

        if (!$gameId || !$postData) {
            throw new Exception("Missing required fields: gameId or postData");
        }

        // Route to the correct game class based on $gameId
        $namespace = "Games\\{$gameId}\\";
        $serverClass = $namespace . "Server";
        $settingsClass = $namespace . "SlotSettings";

        // Check if classes exist
        if (!class_exists($settingsClass)) {
            throw new Exception("SlotSettings class not found for game: $gameId");
        }

        if (!class_exists($serverClass)) {
            throw new Exception("Server class not found for game: $gameId");
        }

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

        // Instantiate logic
        $slotSettings = new $settingsClass($settingsData);
        $server = new $serverClass();

        // Calculate
        $responseJson = $server->get($postData, $slotSettings);

        // Send back to TypeScript
        $connection->send($responseJson);
    } catch (Exception $e) {
        $timestamp = date('Y-m-d H:i:s');
        $errorLog = "[$timestamp] WORKER_EXCEPTION: " . $e->getMessage() .
            " in " . $e->getFile() . ":" . $e->getLine() .
            "\nStack trace:\n" . $e->getTraceAsString() . "\n";

        error_log($errorLog, 3, ERROR_LOG);
        error_log($errorLog, 3, WORKER_ERROR_LOG);

        // Send error response back to client
        $errorResponse = json_encode([
            'error' => 'Internal server error',
            'message' => $e->getMessage(),
            'timestamp' => $timestamp
        ]);

        try {
            $connection->send($errorResponse);
        } catch (Exception $sendException) {
            error_log("Failed to send error response: " . $sendException->getMessage(), 3, ERROR_LOG);
        }
    } catch (Error $e) {
        $timestamp = date('Y-m-d H:i:s');
        $errorLog = "[$timestamp] WORKER_FATAL_ERROR: " . $e->getMessage() .
            " in " . $e->getFile() . ":" . $e->getLine() .
            "\nStack trace:\n" . $e->getTraceAsString() . "\n";

        error_log($errorLog, 3, ERROR_LOG);
        error_log($errorLog, 3, WORKER_ERROR_LOG);
        error_log($errorLog, 3, FATAL_ERROR_LOG);

        // Send error response back to client
        $errorResponse = json_encode([
            'error' => 'Internal server error',
            'message' => 'Fatal error occurred',
            'timestamp' => $timestamp
        ]);

        try {
            $connection->send($errorResponse);
        } catch (Exception $sendException) {
            error_log("Failed to send error response: " . $sendException->getMessage(), 3, ERROR_LOG);
        }
    }
};

// Add comprehensive worker error handling
$worker->onError = function ($connection, $code, $msg) {
    $timestamp = date('Y-m-d H:i:s');
    $errorLog = "[$timestamp] CONNECTION_ERROR: Code $code - $msg\n";
    error_log($errorLog, 3, ERROR_LOG);
    error_log($errorLog, 3, WORKER_ERROR_LOG);
};

$worker->onWorkerError = function ($worker, $pid, $exit_code, $signal) {
    $timestamp = date('Y-m-d H:i:s');
    $errorLog = "[$timestamp] WORKER_PROCESS_ERROR: Worker " . ($worker->id ?? 'unknown') .
        " died with PID $pid, exit code $exit_code, signal $signal\n";
    error_log($errorLog, 3, ERROR_LOG);
    error_log($errorLog, 3, WORKER_PROCESS_LOG);
    error_log($errorLog, 3, FATAL_ERROR_LOG); // Critical process errors should also go here
};

$worker->onConnect = function ($connection) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] NEW_CONNECTION: " . $connection->id . " from " . $connection->getRemoteAddress() . "\n";
    // Uncomment to log connections
    // error_log($logMessage, 3, ERROR_LOG);
};

$worker->onClose = function ($connection) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] CONNECTION_CLOSED: " . $connection->id . "\n";
    // Uncomment to log disconnections
    // error_log($logMessage, 3, ERROR_LOG);
};

// Log that we're starting
error_log("\n=== WORKERMAN STARTING ===\n", 3, ERROR_LOG);

Worker::runAll();
