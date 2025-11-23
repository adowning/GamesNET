<?php

// Development server with auto-reload functionality
// This script watches for file changes and restarts the main server
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

$projectRoot = __DIR__;
$mainServerFile = $projectRoot . '/start.php start';
$watchPaths = [
    $projectRoot . '/Models/',
    $projectRoot . '/BaseSlotSettings.php',
    $projectRoot . '/GameReelConfig.php',
    $projectRoot . '/Log.php'
];

// Add all game directories
foreach (glob($projectRoot . '/*', GLOB_ONLYDIR) as $dir) {
    if (strpos($dir, 'NET') !== false) {
        $watchPaths[] = $dir . '/';
    }
}

echo "Starting development server with auto-reload...\n";
echo "Watching paths:\n";
foreach ($watchPaths as $path) {
    echo "  - $path\n";
}
echo "\n";

// Function to check if files have changed
function getFileHashes($paths)
{
    $hashes = [];
    foreach ($paths as $path) {
        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                $hashes[$file] = hash_file('md5', $file);
            }
        } elseif (is_file($path)) {
            $hashes[$path] = hash_file('md5', $path);
        }
    }
    return $hashes;
}

// Function to restart the server
function restartServer($mainServerFile)
{
    global $processes;

    // Kill existing processes
    foreach ($processes as $process) {
        if (process_exists($process['pid'])) {
            echo "Killing existing server process: {$process['pid']}\n";
            exec("kill {$process['pid']}");
        }
    }

    // Start new process in background
    echo "Starting new server process...\n";
    $cmd = "php " . escapeshellarg($mainServerFile) . " start" . " > /dev/null 2>&1 & echo $!";
    $pid = exec($cmd);

    $processes[] = [
        'pid' => $pid,
        'start_time' => time()
    ];

    echo "Server started with PID: $pid\n";
    return $processes;
}

// Function to check if process exists
function process_exists($pid)
{
    $output = exec("ps -p $pid");
    return !empty($output);
}

// Initialize
$processes = [];
$lastHashes = getFileHashes($watchPaths);

echo "Initial server startup...\n";
$processes = restartServer($mainServerFile);

// Main loop - check for file changes every 2 seconds
while (true) {
    sleep(2);

    $currentHashes = getFileHashes($watchPaths);

    // Compare hashes
    $changed = false;
    foreach ($currentHashes as $file => $hash) {
        if (!isset($lastHashes[$file])) {
            echo "New file detected: $file\n";
            $changed = true;
        } elseif ($lastHashes[$file] !== $hash) {
            echo "File changed: $file\n";
            $changed = true;
        }
    }

    // Check for deleted files
    foreach ($lastHashes as $file => $hash) {
        if (!isset($currentHashes[$file])) {
            echo "File deleted: $file\n";
            $changed = true;
        }
    }

    if ($changed) {
        echo "Changes detected! Restarting server...\n";
        $processes = restartServer($mainServerFile);
        $lastHashes = $currentHashes;

        // Wait a moment before continuing to check
        sleep(1);
    }
}
