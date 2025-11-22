#!/bin/bash

# Robust SlotSettings Refactoring Script
# This script handles the unique namespace structure: namespace VanguardLTE\Games\{GameName} { }

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BACKUP_DIR=".slotsettings_backups"
GAMES_TO_SKIP=("StarBurstNET" "HalloweenJackNET")  # Files to skip (user will manually edit)

# Log file
LOG_FILE="slotsettings_refactor.log"

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
}

# Function to check if file should be skipped
should_skip_file() {
    local game_name="$1"
    for skip_game in "${GAMES_TO_SKIP[@]}"; do
        if [[ "$game_name" == "$skip_game" ]]; then
            return 0  # True - skip this file
        fi
    done
    return 1  # False - don't skip
}

# Function to create backup directory
setup_backup_dir() {
    if [[ ! -d "$BACKUP_DIR" ]]; then
        mkdir -p "$BACKUP_DIR"
        log "Created backup directory: $BACKUP_DIR"
    fi
}

# Function to create PHP refactoring script
create_php_refactor_script() {
    cat > temp_refactor.php << 'PHPEOF'
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

// Build regex pattern for shared methods
$sharedMethodsPattern = '/^\s*(public|private|protected)?\s*function\s+(' . implode('|', array_map('preg_quote', $sharedMethods)) . ')\s*\(/';

// Parse the content
$lines = explode("\n", $content);
$newLines = [];
$inClass = false;
$inMethod = false;
$braceCount = 0;
$methodIndent = '';

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
    if ($inClass && !$inMethod && preg_match($sharedMethodsPattern, $trimmed)) {
        $inMethod = true;
        $methodIndent = preg_replace('/^(\s*).*$/', '$1', $line);
        $braceCount = 0;
        continue;
    }
    
    // Track braces in methods we're skipping
    if ($inMethod) {
        $braceCount += substr_count($trimmed, '{') - substr_count($trimmed, '}');
        
        // If brace count is 0 or less, we've exited the method
        if ($braceCount <= 0) {
            $inMethod = false;
            $methodIndent = '';
        }
        continue;
    }
    
    // Keep everything else
    $newLines[] = $line;
}

// Write the refactored content
$refactoredContent = implode("\n", $newLines);

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
PHPEOF
}

# Function to validate PHP syntax
validate_php_syntax() {
    local file="$1"
    if php -l "$file" > /dev/null 2>&1; then
        return 0
    else
        return 1
    fi
}

# Function to restore from backup
restore_from_backup() {
    local game_name="$1"
    local backup_file="$BACKUP_DIR/${game_name}_SlotSettings.php.backup"
    local original_file="${game_name}NET/SlotSettings.php"
    
    if [[ -f "$backup_file" ]]; then
        cp "$backup_file" "$original_file"
        log_warning "Restored $game_name from backup"
    fi
}

# Main refactoring function
refactor_slotsettings() {
    local game_name="$1"
    local file_path="${game_name}NET/SlotSettings.php"
    
    if [[ ! -f "$file_path" ]]; then
        log_warning "File not found: $file_path"
        return 1
    fi
    
    log "Processing $game_name..."
    
    # Create backup
    cp "$file_path" "$BACKUP_DIR/${game_name}_SlotSettings.php.backup"
    log "Created backup: $BACKUP_DIR/${game_name}_SlotSettings.php.backup"
    
    # Run PHP refactoring script
    if php temp_refactor.php "$file_path"; then
        # Validate syntax
        if validate_php_syntax "$file_path"; then
            log_success "Successfully refactored $game_name"
            return 0
        else
            log_error "PHP syntax error in $game_name, restoring backup..."
            restore_from_backup "$game_name"
            return 1
        fi
    else
        log_error "Failed to refactor $game_name, restoring backup..."
        restore_from_backup "$game_name"
        return 1
    fi
}

# Main execution
main() {
    log "Starting SlotSettings refactoring..."
    log "Games to skip: ${GAMES_TO_SKIP[*]}"
    
    setup_backup_dir
    create_php_refactor_script
    
    local success_count=0
    local fail_count=0
    
    # Find all game directories and their SlotSettings files
    while IFS= read -r -d '' file; do
        # Extract game name from file path like ./GameNameNET/SlotSettings.php
        local file_path="$file"
        local game_dir=$(dirname "$file_path")
        local game_name=$(basename "$game_dir" "NET")
        
        # Skip manually edited files
        if should_skip_file "$game_name"; then
            log "Skipping $game_name (manually edited)"
            continue
        fi
        
        log "Found game: $game_name at $file_path"
        if refactor_slotsettings "$game_name"; then
            ((success_count++))
        else
            ((fail_count++))
        fi
    done < <(find . -name "SlotSettings.php" -not -path "./.orgiginals/*" -not -path "./.slotsettings_backups/*" -print0)
    
    # Cleanup
    rm -f temp_refactor.php
    
    log "Refactoring complete!"
    log "Successful: $success_count"
    log "Failed: $fail_count"
    
    if [[ $fail_count -eq 0 ]]; then
        log_success "All files refactored successfully!"
        return 0
    else
        log_error "Some files failed to refactor. Check the log for details."
        return 1
    fi
}

# Run main function
main "$@"