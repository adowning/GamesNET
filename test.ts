// Comprehensive TypeScript type definitions for Workerman server communication

// ============================================================================
// MAIN PAYLOAD STRUCTURE (from start.php)
// ============================================================================
import { readdir, writeFile } from 'fs/promises';
import getPhpRequest, { getShopPercentage, getUserBalance } from './payloads'
import * as net from 'net';

const SPIN_COUNT = 1
interface ServerPayload {
    gameId: string;                           // Game identifier (e.g., "TheWolfsBaneNET")
    postData: PostData;                       // Data passed to Server::get()
    state: GameState;                         // Game state information
}

// ============================================================================
// POST DATA (what gets passed to Server::get($postData, &$slotSettings))
// ============================================================================
/*
            "action": "init",
            "sessid": "",
            "gameId": "flowers_mobile_html",
            "token": "pD8q7hkxeqIrTlog4LHerSAxHfj70z7u.YJmHoIHEHSbzyLVHuwMG2qHlBHXmuQlK7wx9JaxYi7U=",
            "wantsfreerounds": "true",
            "freeroundmode": "false",
            "wantsreels": "true",
            "no-cache": "1763840515851",
            "sessionId": "undefined"
*/
interface PostData {
    // Core action and event identification
    action: 'init' | 'spin' | 'freespin' | 'initfreespin' | 'initbonus' |
    'bonusaction' | 'endbonus' | 'paytable' | 'reloadbalance';
    // slotEvent?: 'bet' | 'freespin' | 'init' | 'initbonus' | 'bonusaction' |
    // 'endbonus' | 'paytable' | 'initfreespin';
    token?: string;
    gameId: string;
    wantsfreerounds: string;
    wantsreels: string;

    // Betting configuration
    bet_denomination?: number;                // Betting denomination (e.g., 0.05, 0.10, etc.)
    bet_betlevel?: number;                    // Bet level multiplier

    // Game-specific fields (vary by game)
    wildwildwest_bonus_pick?: string;         // Bonus game pick selection (e.g., "2" or "4")
    [key: string]: any;                       // Additional game-specific parameters
}

// ============================================================================
// GAME STATE INFORMATION
// ============================================================================

interface GameState {
    goldsvetData: {
        paytable: Record<string, number[]>;     // Symbol payout table
        symbol_game: number[];                  // Available symbol IDs
        denomination?: string;                  // Denomination configuration
        [key: string]: any;                     // Additional game-specific state
    };
}

// ============================================================================
// SLOT SETTINGS STRUCTURE (constructor parameter)
// ============================================================================

interface SlotSettings {
    // User information
    user: User | UserData;

    // Game information  
    game: Game | GameData;

    // Shop information
    shop: Shop | ShopData;

    // Jackpot data
    jpgs: JPG[] | JPGData[];

    // Game data (persistent across requests)
    gameData: Record<string, any>;

    // Static game data (persisted to database)
    gameDataStatic: Record<string, any>;

    // Banking and logging
    bankerService?: any;
    betLogs?: any[];

    // Slot identification
    slotId?: string;
    playerId?: string;
    balance?: number;
    jackpots?: Record<string, any>;

    // Reel strips data
    reelStrips: {
        base?: Record<string, number[]>;        // Base reel strips
        bonus?: Record<string, number[]>;       // Bonus reel strips
    };
}

// ============================================================================
// USER MODEL
// ============================================================================

interface User {
    id: number;
    balance: number;
    shop_id: number;
    count_balance: number;
    address: number;
    session: string;
    is_blocked: boolean;
    status: 'active' | 'banned';
    remember_token?: string;
    last_bid?: string;
}

// Alternative data structure format (when passed as array)
interface UserData {
    id?: number;
    balance?: number;
    shop_id?: number;
    count_balance?: number;
    address?: number;
    session?: string;
    is_blocked?: boolean;
    status?: string;
    remember_token?: string;
    last_bid?: string;
    [key: string]: any;
}

// ============================================================================
// GAME MODEL  
// ============================================================================

interface Game {
    id: number;
    name: string;
    shop_id: number;
    stat_in: number;
    stat_out: number;
    bids: number;
    denomination: number;
    slotViewState: string;
    bet: string;                              // Comma-separated bet values
    jp_config: Record<string, any>;           // Jackpot configuration
    rezerv: number;
    view: boolean;
    advanced: string;
}

// Alternative data structure format (when passed as array)
interface GameData {
    id?: number;
    name?: string;
    shop_id?: number;
    stat_in?: number;
    stat_out?: number;
    bids?: number;
    denomination?: number;
    slotViewState?: string;
    bet?: string;
    jp_config?: Record<string, any>;
    rezerv?: number;
    view?: boolean;
    advanced?: string;
    [key: string]: any;
}

// ============================================================================
// SHOP MODEL
// ============================================================================

interface Shop {
    id: number;
    max_win: number;
    percent: number;
    is_blocked: boolean;
    currency: string;
}

// Alternative data structure format (when passed as array)
interface ShopData {
    id?: number;
    max_win?: number;
    percent?: number;
    is_blocked?: boolean;
    currency?: string;
    [key: string]: any;
}

// ============================================================================
// JACKPOT MODEL
// ============================================================================

interface JPG {
    id: number;
    shop_id: number;
    balance: number;
    percent: number;
    user_id?: number;
    start_balance: number;
}

// Alternative data structure format (when passed as array)
interface JPGData {
    id?: number;
    shop_id?: number;
    balance?: number;
    percent?: number;
    user_id?: number;
    start_balance?: number;
    [key: string]: any;
}

// ============================================================================
// RESPONSE STRUCTURE
// ============================================================================

interface ServerResponse {
    response: string;
    state: {
        GameDenom: number;
        ReelsType: string;
        freeState?: string;
        slotLines: number;
        slotBet: number;
        totalFreeGames: number;
        currentFreeGames: number;
        Balance: number;
        afterBalance: number;
        bonusWin: number;
        totalWin: number;
        winLines: any[];
        Jackpots: any;
        reelsSymbols: any;
        [key: string]: any;
    };
}


const stripAnsi = (str: string) => str.replace(/\x1B\[[0-9;]*[mK]/g, '');

export class PHPWorkerClient {
    private host: string;
    private port: number;

    constructor(host: string = '127.0.0.1', port: number = 8787) {
        this.host = host;
        this.port = port;
    }
    private isCompleteJSON(buffer: string): boolean {
        // Remove ANSI codes first
        const clean = stripAnsi(buffer);

        // Count braces to ensure JSON completeness
        const openBraces = (clean.match(/{/g) || []).length;
        const closeBraces = (clean.match(/}/g) || []).length;

        // Must have balanced braces and end properly
        return openBraces === closeBraces &&
            openBraces > 0 &&
            /}\s*$/.test(clean.trim());
    }
    private attemptDataSalvage(buffer: string): any | null {
        try {
            // Attempt to extract partial JSON or find valid JSON segments
            const cleanBuffer = stripAnsi(buffer);

            // Try to find the longest valid JSON prefix
            for (let i = cleanBuffer.length; i > 0; i--) {
                try {
                    const partial = cleanBuffer.substring(0, i);
                    const testParse = JSON.parse(partial);

                    // If we get here, partial JSON parsed successfully
                    console.log(`Salvaged ${i} characters of ${cleanBuffer.length} total`);
                    return testParse;
                } catch {
                    continue; // Try shorter prefix
                }
            }
        } catch (error) {
            console.error("Salvage attempt failed:", error);
        }
        return null;
    }
    /**
     * Sends a request to the PHP engine and awaits the JSON response.
     */
    public async execute(payload: ServerPayload): Promise<any> {
        return new Promise((resolve, reject) => {
            const client = new net.Socket();
            let buffer = '';
            client.connect(this.port, this.host, () => {
                // Send payload with a newline delimiter as expected by Workerman Text protocol
                client.write(JSON.stringify(payload) + "\n");
            });

            client.on('data', (data: any) => {
                buffer += data.toString();
                // Check if we have a complete JSON object (simple heuristic for this use case)
                // In production, you might want strict framing, but Workerman usually sends in one go for small payloads.
                if (this.isCompleteJSON(buffer)) {
                    try {
                        // const response = JSON.parse(buffer);

                        // 3. Clean and Parse
                        try {
                            const cleanString = stripAnsi(buffer);
                            const parsedData = JSON.parse(cleanString);
                            resolve(parsedData)
                            client.destroy(); // Close connection after response

                        } catch (error) {
                            console.log(buffer)
                            console.error("Parsing error:", error);
                            const salvageAttempt = this.attemptDataSalvage(buffer);
                            if (salvageAttempt) {
                                resolve(salvageAttempt);
                                client.destroy();
                                return;
                            }
                        }
                        client.destroy(); // Close connection after response
                        // resolve(buffer);
                    } catch (e) {
                        console.log(e)
                        throw e
                        // Wait for more data if JSON is incomplete
                    }
                }

            });

            client.on('error', (err: any) => {
                console.log(err)
                client.destroy();
                reject(err);
            });

            client.on('close', (reason: string) => {
                if (reason)
                    console.log('close', stripAnsi(reason))
                // if (!reason) console.log('closed no reason')
                // If connection closes without resolving (and no error), handle strictly if needed
            });
        });
    }
}

const phpClient = new PHPWorkerClient();
async function sleep(seconds: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}

const getDirs = async (): Promise<string[]> => {
    let items = await readdir(process.cwd() + '/Games', {});
    items = items
        .filter((item) => item.includes('NET'))
        .sort();
    return items;
};
async function main() {
    await writeFile('./logs/worker_errors.log', '');
    await writeFile('./logs/server.log', '');
    await writeFile('./logs/run.log', '');
    await writeFile('./logs/fatal_errors.log', '');
    // Hardcoded array of game names to test
    // const games = ['','','','','GoBananasNET', 'SpaceWarsNET', 'StarBurstNET']
    let games = await getDirs()
    // Initialize  global totals across all games
    let globalWinTotal = 0
    let globalSpentTotal = 0
    let startingBalance = getUserBalance()
    let globalUserBalance = getUserBalance()
    let shopPercentage = getShopPercentage()

    console.log('=== MULTI-GAME TEST EXECUTION STARTED ===')
    console.log('Starting global user balance:', globalUserBalance)
    console.log('Games to test:', games.join(', '))
    console.log('Spins per game:', SPIN_COUNT)
    console.log('=====================================')
    // games = ['FlowersNET']
    // games = ['DazzleMeNET']
    // Outer loop: iterate through each game
    for (const currentGame of games) {
        console.log(`\n--- Processing Game: ${currentGame} ---`)

        let gameWinTotal = 0
        let gameSpentTotal = 0
        // let gameUserBalance = globalUserBalance // Start with global balance

        // Step 1: Execute init and paytable commands
        const commands = ['init', 'paytable']
        for (var x = 0; x < 2; x++) {
            let mydata = getPhpRequest(commands[x], currentGame, globalUserBalance)
            console.log(`  Executing ${commands[x]} for ${currentGame}...`)
            const phpResponse = await phpClient.execute(mydata);
            if (x == 0)
                // console.log('length of response ', phpResponse.response)

                if (phpResponse.responseEvent === 'error') {
                    console.error(`PHP Logic Error for ${currentGame} ${commands[x]}:`, phpResponse);
                    return phpResponse; // Return the error string to client
                }
            sleep(.5)
        }

        // Step 2: Execute SPIN_COUNT spins for this game
        console.log(`  Starting ${SPIN_COUNT} spins for ${currentGame}...`)
        for (var n = 0; n < SPIN_COUNT; n++) {
            let mydata = getPhpRequest('spin', currentGame, globalUserBalance)
            // console.log(`  executing spin #${n} for ${currentGame}`)
            console.log(mydata.game)
            const phpResponse = await phpClient.execute(mydata);
            // Update balances for this game
            const betAmount = mydata.postData.bet_betlevel * mydata.postData.bet_denomination
            globalUserBalance -= betAmount
            gameSpentTotal += betAmount

            if (phpResponse.responseEvent === 'error') {
                console.error(`PHP Logic Error for ${currentGame} spin #${n}:`, phpResponse);
                return phpResponse; // Return the error string to client
            }

            // console.log(JSON.stringify(phpResponse.response.substring(0, 10), null, 2));
            let reportWin
            if (phpResponse.state) {
                if (phpResponse.state.logReport[0]) {
                    reportWin = phpResponse.state.logReport[0].reportWin

                }
                if (typeof reportWin === 'number') {
                    // console.log('win: ', JSON.stringify(reportWin, null, 2))
                    gameWinTotal += reportWin
                    globalUserBalance += reportWin
                }
                const cleanString = JSON.stringify(phpResponse.state).replace(/\u001b\[\d+m/g, "");

                // 2. Fix invalid JSON syntax (The input has unquoted keys like slotId:)
                // We verify it looks like an object, then use Function to evaluate it safely-ish
                // Note: This input specifically had "Array" written as text, which breaks JS, 
                // so we replace it with empty brackets []
                const fixedString = cleanString.replace(/: Array/g, ": []");

                try {
                    // Evaluate the object literal string to a JS Object
                    const jsonObject = new Function("return " + fixedString)();

                    // Convert back to pretty JSON string
                    // console.log(JSON.stringify(jsonObject, null, 2));
                } catch (e) {
                    console.error("Could not parse JSON:", e);
                    console.log(cleanString); // Fallback to just the clean string
                }
            }
            sleep(.5)
        }

        // Update global totals
        globalWinTotal += gameWinTotal
        globalSpentTotal += gameSpentTotal
        globalUserBalance = globalUserBalance

        // Display results for this game
        console.log(`  --- ${currentGame} Results ---`)
        console.log(`  Game Win Total: ${gameWinTotal}`)
        console.log(`  Game Spent Total: ${gameSpentTotal}`)
        console.log(`  Game Net Result: ${gameWinTotal - gameSpentTotal}`)
        console.log(`  Ending Game Balance: ${globalUserBalance}`)
        console.log(`  ------------------------`)
    }


    // Final summary across all games
    console.log('\n=== FINAL MULTI-GAME RESULTS ===')
    console.log('Total spent across all games:', globalSpentTotal)
    console.log('Total winnings across all games:', globalWinTotal)
    console.log('Net result across all games:', globalWinTotal - globalSpentTotal)
    console.log(`Starting Balance: `, startingBalance)
    console.log('Final global balance:', globalUserBalance)
    console.log('Shop Percentage:', shopPercentage)
    console.log('===============================')
    process.exit()

}


await main()
