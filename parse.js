/**
 * Script to extract games ending with 'GT' and convert to JSON format
 * Fixed to process games with shop_id = 0 and handles SQL parsing better
 */

const fs = require("fs");
const path = require("path");

// Table column names in order
const COLUMN_NAMES = [
  "id",
  "name",
  "title",
  "shop_id",
  "jpg_id",
  "label",
  "device",
  "gamebank",
  "chanceFirepot1",
  "chanceFirepot2",
  "chanceFirepot3",
  "fireCount1",
  "fireCount2",
  "fireCount3",
  "lines_percent_config_spin",
  "lines_percent_config_spin_bonus",
  "lines_percent_config_bonus",
  "lines_percent_config_bonus_bonus",
  "rezerv",
  "cask",
  "advanced",
  "bet",
  "scaleMode",
  "slotViewState",
  "view",
  "denomination",
  "category_temp",
  "original_id",
  "bids",
  "stat_in",
  "stat_out",
  "created_at",
  "updated_at",
  "current_rtp",
  "rtp_stat_in",
  "rtp_stat_out",
];

/**
 * Parse a single SQL value and convert to appropriate JavaScript type
 */
function parseSQLValue(value) {
  // Remove outer quotes and handle NULL
  value = value.trim();

  if (value === "NULL" || value === "null") {
    return null;
  }

  if (value === "''" || value === "") {
    return "";
  }

  // Remove outer quotes
  if (
    (value.startsWith("'") && value.endsWith("'")) ||
    (value.startsWith('"') && value.endsWith('"'))
  ) {
    value = value.slice(1, -1);

    // Try to parse as JSON (for configuration fields)
    try {
      // Check if it looks like JSON
      if (
        (value.startsWith("{") && value.endsWith("}")) ||
        (value.startsWith("[") && value.endsWith("]"))
      ) {
        return JSON.parse(value);
      }
    } catch (e) {
      // If JSON parsing fails, return as string
      console.warn(`Failed to parse JSON: ${value.substring(0, 50)}...`);
    }

    return value;
  }

  // Try to parse as number
  if (!isNaN(value) && value !== "") {
    return value.includes(".") ? parseFloat(value) : parseInt(value, 10);
  }

  // Return as string
  return value;
}

/**
 * Split SQL values handling nested quotes and commas more robustly
 */
function splitSQLValues(sqlValues) {
  const values = [];
  let current = "";
  let inQuotes = false;
  let quoteChar = "";
  let parenDepth = 0;
  let braceDepth = 0;
  let bracketDepth = 0;

  for (let i = 0; i < sqlValues.length; i++) {
    const char = sqlValues[i];

    // Handle quotes
    if (!inQuotes && (char === "'" || char === '"')) {
      inQuotes = true;
      quoteChar = char;
      current += char;
    } else if (inQuotes && char === quoteChar) {
      // Check if it's escaped
      if (sqlValues[i + 1] === quoteChar) {
        current += char + char;
        i++; // Skip next char
      } else {
        inQuotes = false;
        current += char;
      }
    } else if (!inQuotes) {
      // Handle nested structures
      if (char === "(") {
        parenDepth++;
        current += char;
      } else if (char === ")") {
        parenDepth--;
        current += char;
      } else if (char === "{") {
        braceDepth++;
        current += char;
      } else if (char === "}") {
        braceDepth--;
        current += char;
      } else if (char === "[") {
        bracketDepth++;
        current += char;
      } else if (char === "]") {
        bracketDepth--;
        current += char;
      } else if (
        char === "," &&
        parenDepth === 0 &&
        braceDepth === 0 &&
        bracketDepth === 0
      ) {
        values.push(current.trim());
        current = "";
      } else {
        current += char;
      }
    } else {
      current += char;
    }
  }

  if (current.trim()) {
    values.push(current.trim());
  }

  return values;
}

/**
 * Extract games from v105.sql with improved parsing
 */
function extractGamesFromV105(data) {
  const games = [];

  // Split by INSERT statements to handle multi-line data better
  const insertStatements = data.split(/INSERT INTO[^;]+VALUES\s*/gi);

  for (const statement of insertStatements) {
    if (!statement.trim() || !statement.includes("(")) continue;

    // Find all value groups in this statement
    const valueGroupPattern = /\(([\s\S]*?)\)(?=\s*,?\s*(?:\n|$|\d+\s*,))/g;
    let match;

    while ((match = valueGroupPattern.exec(statement)) !== null) {
      const valuesStr = match[1];

      try {
        const values = splitSQLValues(valuesStr);

        // Parse values into object
        const gameObj = {};
        COLUMN_NAMES.forEach((column, index) => {
          gameObj[column] = values[index] ? parseSQLValue(values[index]) : null;
        });

        // Debug output to see what we're finding
        if (gameObj.name) {
          console.log(
            `Debug - Game found: ${gameObj.name}, shop_id: ${
              gameObj.shop_id
            }, ends with GT: ${gameObj.name.endsWith("NET")}`
          );
        }

        // Check if it's a GT game AND has shop_id = 0 (corrected criteria)
        if (
          gameObj.name &&
          typeof gameObj.name === "string" &&
          gameObj.name.endsWith("NET") &&
          gameObj.shop_id === 0
        ) {
          // Add metadata
          //   gameObj._extracted = {
          //     original_line: match[0],
          //     extracted_at: new Date().toISOString(),
          //     filter_applied: "shop_id = 0 AND name ends with GT",

          delete gameObj.chanceFirepot1;
          delete gameObj.chanceFirepot2;
          delete gameObj.chanceFirepot3;
          delete gameObj.fireCount1;
          delete gameObj.fireCount2;
          delete gameObj.fireCount3;

          games.push(gameObj);
          console.log(
            `✓ Added game: ${gameObj.name} (shop_id: ${gameObj.shop_id})`
          );
        }
      } catch (error) {
        console.error(`Error parsing game data:`, error.message);
        console.error(`Problematic data: ${valuesStr.substring(0, 100)}...`);
      }
    }
  }

  return games;
}

/**
 * Get all NET games from filesystem directories (fallback)
 */
function getNetGamesFromDirectories() {
  const currentDir = __dirname;
  const netGames = [];

  try {
    const items = fs.readdirSync(currentDir, { withFileTypes: true });

    for (const item of items) {
      if (item.isDirectory() && item.name.endsWith("NET")) {
        const gameObj = {};
        COLUMN_NAMES.forEach((column) => {
          gameObj[column] = null; // Set all to null as fallback
        });

        // Set known values with shop_id = 1 for NET games
        gameObj.id = 0; // Unknown ID from directory listing
        gameObj.name = item.name;
        gameObj.title = item.name
          .replace(/NET$/, "")
          .replace(/([A-Z])/g, " $1")
          .trim();
        gameObj.shop_id = 1; // Force shop_id = 1 for NET games
        gameObj.device = 1;
        gameObj.gamebank = "slots";
        gameObj.view = 1;
        gameObj.denomination = 1.0;
        gameObj.bids = 0;
        gameObj.stat_in = 0.0;
        gameObj.stat_out = 0.0;
        gameObj.current_rtp = 0.0;
        gameObj.rtp_stat_in = 0.0;
        gameObj.rtp_stat_out = 0.0;
        gameObj.updated_at = new Date()
          .toISOString()
          .slice(0, 19)
          .replace("T", " ");

        // Add metadata
        // gameObj._extracted = {
        //   source: "filesystem_directory",
        //   extracted_at: new Date().toISOString(),
        //   filter_applied: "NET games from directories (shop_id = 1)",
        // };

        netGames.push(gameObj);
        console.log(`✓ Added NET game from directory: ${gameObj.name}`);
      }
    }
  } catch (error) {
    console.error("Error reading directories:", error);
  }

  return netGames.sort((a, b) => a.name.localeCompare(b.name));
}

function main() {
  const dataSource = "v105.sql";
  let inputData = "";

  // Only read from v105.sql
  try {
    const filePath = path.join(__dirname, dataSource);
    if (fs.existsSync(filePath)) {
      inputData = fs.readFileSync(filePath, "utf8");
      console.log(`✓ Found data in ${dataSource}`);
      console.log(
        `File size: ${(inputData.length / 1024 / 1024).toFixed(2)} MB`
      );
    } else {
      console.log(`✗ ${dataSource} not found`);
    }
  } catch (error) {
    console.log(`✗ Could not read ${dataSource}:`, error.message);
  }

  let games = [];

  if (inputData.trim()) {
    console.log("Starting extraction from v105.sql...");
    games = extractGamesFromV105(inputData);
    console.log(
      `✓ Extracted ${games.length} GT games with shop_id = 0 from ${dataSource}`
    );

    // If no GT games found, try NET games from directories
    if (games.length === 0) {
      console.log(
        "No GT games found, checking for NET games in directories..."
      );
      games = getNetGamesFromDirectories();
      console.log(`✓ Found ${games.length} NET games from directories`);
    }
  } else {
    console.log("No data found in v105.sql, using filesystem directories");
    games = getNetGamesFromDirectories();
    console.log(`✓ Found ${games.length} NET games from directories`);
  }

  if (games.length === 0) {
    console.log("❌ No games found matching criteria.");
    console.log(
      'Expected: Games ending with "GT" and shop_id = 0 OR games ending with "NET" from directories'
    );
    return;
  }

  // Sort games by name
  games.sort((a, b) => a.name.localeCompare(b.name));

  // Write JSON to file
  const outputFilename = "netenet_games.json";
  const timestamp = new Date().toISOString();

  let jsonContent = JSON.stringify(games, null, 2);

  // Add header comment
  jsonContent = `// Games extracted from database
// Total games found: ${games.length}
// Data source: ${dataSource} and/or filesystem directories
// Filter applied: shop_id = 0 AND name ends with 'GT' (from DB) OR shop_id = 1 AND name ends with 'NET' (from directories)
// Generated on: ${timestamp}
//
// This is a JSON array of game objects with the following structure:
// ${JSON.stringify(COLUMN_NAMES, null, 2)}

${jsonContent}`;

  try {
    fs.writeFileSync(outputFilename, jsonContent, "utf8");
    console.log(`✓ JSON results saved to: ${outputFilename}`);
  } catch (error) {
    console.error(`✗ Error writing file:`, error.message);
  }

  // Print summary to console
  console.log("\n" + "=".repeat(70));
  console.log("🎮 EXTRACTION RESULTS");
  console.log("=".repeat(70));
  console.log(`Total games found: ${games.length}`);
  console.log(`Data sources: ${dataSource} and/or filesystem directories`);
  console.log(
    `Filters applied: shop_id = 0 + GT games OR shop_id = 1 + NET games`
  );
  console.log(`Output format: JSON`);
  console.log(`Output file: ${outputFilename}`);

  console.log("\nGames extracted:");
  console.log("-".repeat(50));

  games.forEach((game, index) => {
    console.log(
      `${(index + 1).toString().padStart(2, "0")}. ${game.name} (${
        game.title || "No title"
      }) - Shop ID: ${game.shop_id}`
    );
    if (game.lines_percent_config_spin) {
      console.log(
        `   RTP Config: ${JSON.stringify(
          game.lines_percent_config_spin
        ).substring(0, 50)}...`
      );
    }
  });

  console.log("-".repeat(50));
  console.log("✓ Complete JSON data extracted successfully");
  console.log("=".repeat(70));
}

main();
