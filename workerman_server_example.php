<?php
// workerman_server_example.php
require_once __DIR__ . '/vendor/autoload.php';

$worker = new Workerman\Worker('http://0.0.0.0:8787');

$worker->onMessage = function ($connection, $request) {
    // Handle GET requests
    if ($request->method() === 'GET') {
        $response = [
            'status' => 'ok',
            'message' => 'Casino Slot Server is running',
            'port' => 8787,
            'version' => '1.0.0',
            'endpoints' => [
                'POST' => '/ for slot calculations',
                'GET' => '/ for server status'
            ]
        ];
        $connection->send(json_encode($response));
        return;
    }

    // Handle POST requests (your slot logic)
    try {
        // Parse incoming JSON data
        $postData = $request->post();

        // Check if post data is already an array (Workerman's default)
        if (is_array($postData)) {
            $data = $postData;
        } else {
            // If it's a string, try to decode it as JSON
            $data = json_decode($postData, true) ?? [];
        }

        // Create state manager for this request  
        $stateManager = new \Services\StateManager();

        // Initialize models from incoming data (these will autoload now!)
        $user = new \Models\User($data['user'] ?? []);
        $game = new \Models\Game($data['game'] ?? []);
        $shop = new \Models\Shop($data['shop'] ?? []);
        $jpg = new \Models\JPG($data['jpg'] ?? []);
        $gameBank = new \Models\GameBank($data['gameBank'] ?? []);

        // Register models with state manager
        $stateManager->registerModel('user', $user);
        $stateManager->registerModel('game', $game);
        $stateManager->registerModel('shop', $shop);
        $stateManager->registerModel('jpg', $jpg);
        $stateManager->registerModel('gameBank', $gameBank);

        // Execute the slot calculation (replace with actual SlotSettings class)
        // $slotSettings = new \BaseSlotSettings($user, $game, $shop, $jpg, $gameBank);
        // $spinResult = $slotSettings->getNewSpin($data['bet'], $lines, $betLevel, $slotEvent);

        // Placeholder result for demonstration
        $spinResult = [
            'win' => 0,
            'spin_result' => ['reels' => [[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            'type' => 'spin'
        ];

        // Mark all models for save (updates their change tracking)
        $stateManager->markAllForSave();

        // Get all changed state data to return
        $changedStates = $stateManager->getChangedModels();

        // Prepare response with spin result and updated state
        $response = [
            'success' => true,
            'spin' => $spinResult,
            'updated_states' => $changedStates,
            'full_states' => $stateManager->getAllStates()
        ];

        // Send response back to TypeScript server
        $connection->send(json_encode($response));
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
        $connection->send(json_encode($response));
    }
};

echo "Starting Casino Slot Server on port 8787...\n";
echo "Server will respond to:\n";
echo "  GET  http://localhost:8787/ - Server status\n";
echo "  POST http://localhost:8787/ - Slot calculations\n\n";

// Run all workers
Workerman\Worker::runAll();
