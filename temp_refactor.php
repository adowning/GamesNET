<?php
/**
 * SlotSettings Refactoring Script
 * Extracts shared methods and updates class to extend BaseSlotSettings
 */

if ($argc < 2) {
    echo "Usage: php temp_refactor.php <file_path>\n";
    exit(1);
}

$filePath = $argv[1];

if (!file_exists($filePath)) {
    echo "Error: File $filePath does not exist\n";
    exit(1);
}

$content = file_get_contents($filePath);
if ($content === false) {
    echo "Error: Cannot read file $filePath\n";
    exit(1);
}

// Extract game name from file path
$pathInfo = pathinfo($filePath);
$gameName = $pathInfo['filename'];

// Define shared methods that should be removed (from BaseSlotSettings)
$sharedMethods = [
    'is_active',
    'SetGameData',
    'GetGameData', 
    'HasGameData',
    'SaveGameData',
    'SetGameDataStatic',
    'GetGameDataStatic',
    'HasGameDataStatic',
    'SaveGameDataStatic',
    'GetBank',
    'SetBank',
    'GetBalance',
    'SetBalance',
    'GetPercent',
    'GetCountBalanceUser',
    'UpdateJackpots',
    'FormatFloat',
    'CheckBonusWin',
    'GetRandomPay',
    'InternalError',
    'InternalErrorSilent',
    'SaveLogReport',
    'GetHistory',
    'GetGambleSettings'
];

// Game-specific methods to keep
$gameSpecificMethods = [
    '__construct',
    'GetSpinSettings',
    'getNewSpin', 
    'GetRandomScatterPos',
    'GetReelStrips'
];

// Parse the content
$lines = explode("\n", $content);
$newLines = [];
$inClass = false;
$inMethod = false;
$currentMethod = '';
$methodIndent = '';
$braceCount = 0;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $trimmed = trim($line);
    
    // Handle class declaration
    if (preg_match('/^\s*class\s+SlotSettings\s*{/', $trimmed)) {
        $newLines[] = "class SlotSettings extends \\Games\\BaseSlotSettings";
        $inClass = true;
        continue;
    }
    
    // Skip shared methods
    if ($inClass && !$inMethod && preg_match('/^\s*(public|private|protected)?\s*function\s+(' . implode('|', array_map('preg_quote', $sharedMethods)) . ')\s*\(/', $trimmed)) {
        $inMethod = true;
        $currentMethod = preg_replace('/^\s*(public|private|protected)?\s*function\s+/', '', $trimmed);
        $currentMethod = preg_replace('/\s*\([^)]*\)\s*{.*$/', '', $currentMethod);
        $methodIndent = preg_replace('/^(\s*).*$/', '$1', $line);
        $braceCount = 0;
        continue;
    }
    
    // Track braces in methods we're skipping
    if ($inMethod) {
        // Count opening braces
        $openBraces = substr_count($trimmed, '{');
        $closeBraces = substr_count($trimmed, '}');
        $braceCount += $openBraces - $closeBraces;
        
        // If brace count is 0 or less, we've exited the method
        if ($braceCount <= 0) {
            $inMethod = false;
            $currentMethod = '';
            $methodIndent = '';
        }
        continue;
    }
    
    // Keep everything else
    $newLines[] = $line;
}

// Handle simplified constructor
$finalLines = [];
$inConstructor = false;
$constructorIndent = '';
$braceCount = 0;

foreach ($newLines as $line) {
    $trimmed = trim($line);
    
    // Handle simplified constructor
    if (preg_match('/^\s*public\s+function\s+__construct\s*\([^)]*\)\s*{/', $trimmed)) {
        $inConstructor = true;
        $constructorIndent = preg_replace('/^(\s*).*$/', '$1', $line);
        $finalLines[] = $constructorIndent . "public function __construct(\$sid, \$playerId)";
        $finalLines[] = $constructorIndent . "{";
        $finalLines[] = $constructorIndent . "    \$settings = [";
        $finalLines[] = $constructorIndent . "        'slotId' => \$sid,";
        $finalLines[] = $constructorIndent . "        'playerId' => \$playerId,";
        $finalLines[] = $constructorIndent . "        'user' => \\VanguardLTE\\User::lockForUpdate()->find(\$playerId),";
        $finalLines[] = $constructorIndent . "        'game' => null, // Will be set by server";
        $finalLines[] = $constructorIndent . "        'shop' => null, // Will be set by server";
        $finalLines[] = $constructorIndent . "        'jpgs' => [],";
        $finalLines[] = $constructorIndent . "        'bank' => null,";
        $finalLines[] = $constructorIndent . "        'gameData' => [],";
        $finalLines[] = $constructorIndent . "        'gameDataStatic' => [],";
        $finalLines[] = $constructorIndent . "        'state' => ['goldsvetData' => []]";
        $finalLines[] = $constructorIndent . "    ];";
        $finalLines[] = $constructorIndent . "    ";
        $finalLines[] = $constructorIndent . "    parent::__construct(\$settings);";
        $finalLines[] = $constructorIndent . "    ";
        $finalLines[] = $constructorIndent . "    // Game-specific initialization";
        
        continue;
    }
    
    // Add other lines
    $finalLines[] = $line;
}

// Write the refactored content
$refactoredContent = implode("\n", $finalLines);

// Ensure proper namespace closing
if (!preg_match('/\}\s*$/', $refactoredContent)) {
    $refactoredContent .= "\n\n}\n";
}

if (file_put_contents($filePath, $refactoredContent) === false) {
    echo "Error: Cannot write to file $filePath\n";
    exit(1);
}

echo "Successfully refactored: $filePath\n";
?>
