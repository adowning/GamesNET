#!/bin/bash

# SlotSettings Refactoring Script (Fixed Version)
# Removes shared methods and converts to extend BaseSlotSettings

echo "Refactoring SlotSettings files to extend BaseSlotSettings..."

# Function to refactor a single file
refactor_file() {
    local file="$1"
    local temp_file="${file}.tmp"
    
    echo "Processing: $file"
    
    # Create backup
    cp "$file" "${file}.backup"
    
    # Step 1: Remove everything between "public function is_active()" and the end of SaveLogReport
    # First, find line numbers for the patterns
    local start_line=$(grep -n "public function is_active()" "$file" | cut -d: -f1)
    local end_line=$(grep -n "'date_time' => \\\Carbon\Carbon::now()" "$file" | cut -d: -f1)
    
    if [ -n "$start_line" ] && [ -n "$end_line" ]; then
        echo "  Removing lines $start_line to $end_line"
        
        # Create file without the shared methods
        head -n $((start_line - 1)) "$file" > "$temp_file"
        tail -n +$((end_line)) "$file" >> "$temp_file"
        
        # Add proper ending for SaveLogReport method if it was cut off
        echo "            ];" >> "$temp_file"
        echo "        }" >> "$temp_file"
        echo "" >> "$temp_file"
    else
        echo "  Warning: Could not find start/end patterns, copying file as-is"
        cp "$file" "$temp_file"
    fi
    
    # Step 2: Change namespace from VanguardLTE\Games\ to Games
    sed -i 's/namespace VanguardLTE\\Games\\/namespace Games\\/g' "$temp_file"
    
    # Step 3: Change class declaration to extend BaseSlotSettings
    sed -i 's/^[[:space:]]*class[[:space:]]*SlotSettings[[:space:]]*$/class SlotSettings extends BaseSlotSettings/' "$temp_file"
    
    # Step 4: Remove the old constructor completely
    # Find the start and end of the old constructor and remove it
    local constr_start=$(grep -n "public function __construct(\$sid, \$playerId)" "$temp_file" | cut -d: -f1)
    
    if [ -n "$constr_start" ]; then
        # Find the matching closing brace
        local brace_count=0
        local constr_end=$constr_start
        
        # Read file from constructor onwards to find the end
        tail -n +$constr_start "$temp_file" | while IFS= read -r line; do
            ((constr_end++))
            # Count braces in the line
            local open_braces=$(echo "$line" | tr -cd '{' | wc -c)
            local close_braces=$(echo "$line" | tr -cd '}' | wc -c)
            ((brace_count += open_braces - close_braces))
            
            if [ $brace_count -eq 0 ] && [ $open_braces -gt 0 ]; then
                break
            fi
        done
        
        if [ $constr_end -gt $constr_start ]; then
            echo "  Removing old constructor (lines $constr_start to $constr_end)"
            head -n $((constr_start - 1)) "$temp_file" > "${temp_file}.tmp2"
            tail -n +$((constr_end + 1)) "$temp_file" >> "${temp_file}.tmp2"
            mv "${temp_file}.tmp2" "$temp_file"
        fi
    fi
    
    # Step 5: Add new constructor
    # Insert new constructor after class declaration
    awk '
    /^class SlotSettings extends BaseSlotSettings/ {
        print
        print "    public function __construct(\$settings)"
        print "    {"
        print "        parent::__construct(\$settings);"
        print "        \$this->initGameSpecificData();"
        print "    }"
        print ""
        next
    }
    { print }
    ' "$temp_file" > "${temp_file}.tmp2"
    mv "${temp_file}.tmp2" "$temp_file"
    
    # Step 6: Add initGameSpecificData method if it doesn't exist
    if ! grep -q "private function initGameSpecificData" "$temp_file"; then
        awk '
        /public function __construct.*\$settings/ {
            # Find end of constructor
            brace_count = 0
            in_constructor = 1
            
            print
            while (in_constructor && getline) {
                print
                # Count braces
                for (i = 1; i <= length($0); i++) {
                    char = substr($0, i, 1)
                    if (char == "{") brace_count++
                    if (char == "}") brace_count--
                }
                if (brace_count == 0 && $0 ~ /[[:space:]]*}[[:space:]]*$/) {
                    in_constructor = 0
                }
            }
            
            # Add new method
            print ""
            print "    private function initGameSpecificData()"
            print "    {"
            print "        // Game-specific data initialization"
            print "        // This method should be overridden in child classes for specific game logic"
            print "    }"
            next
        }
        { print }
        ' "$temp_file" > "${temp_file}.tmp2"
        mv "${temp_file}.tmp2" "$temp_file"
    fi
    
    # Step 7: Clean up trailing whitespace
    sed -i 's/[[:space:]]*$//' "$temp_file"
    
    # Replace original file
    mv "$temp_file" "$file"
    
    echo "Completed: $file"
}

# Function to process all SlotSettings files
process_all_files() {
    # Find all SlotSettings.php files (excluding backups and originals)
    find . -name "SlotSettings.php" -not -path "./.orgiginals/*" -not -name "*.backup" -not -path "./BaseSlotSettings.php" | while read -r file; do
        refactor_file "$file"
    done
}

# Main execution
if [ "$1" = "--all" ]; then
    process_all_files
    echo ""
    echo "All files processed. Backups created with .backup extension."
elif [ -n "$1" ] && [ -f "$1" ]; then
    refactor_file "$1"
    echo ""
    echo "File processed. Backup created with .backup extension."
else
    echo "Usage:"
    echo "  $0 <file>     # Process specific file"
    echo "  $0 --all      # Process all SlotSettings.php files"
    echo ""
    echo "Examples:"
    echo "  $0 WingsOfRichesNET/SlotSettings.php"
    echo "  $0 --all"
    echo ""
    echo "Note: Original files will be backed up with .backup extension"
fi
