// Comprehensive TypeScript type definitions for Workerman server communication

// ============================================================================
// MAIN PAYLOAD STRUCTURE (from start.php)
// ============================================================================

interface ServerPayload {
    gameId: string;                           // Game identifier (e.g., "TheWolfsBaneNET")
    postData: PostData;                       // Data passed to Server::get()
    state: GameState;                         // Game state information
}

// ============================================================================
// POST DATA (what gets passed to Server::get($postData, &$slotSettings))
// ============================================================================

interface PostData {
    // Core action and event identification
    action: 'init' | 'spin' | 'freespin' | 'initfreespin' | 'initbonus' |
    'bonusaction' | 'endbonus' | 'paytable' | 'reloadbalance';
    slotEvent?: 'bet' | 'freespin' | 'init' | 'initbonus' | 'bonusaction' |
    'endbonus' | 'paytable' | 'initfreespin';

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
    responseEvent: string;
    responseType: string;
    serverResponse: {
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

import * as net from 'net';

export class PHPWorkerClient {
    private host: string;
    private port: number;

    constructor(host: string = '127.0.0.1', port: number = 8787) {
        this.host = host;
        this.port = port;
    }

    /**
     * Sends a request to the PHP engine and awaits the JSON response.
     */
    public async execute(payload: ServerPayload): Promise<any> {
        return new Promise((resolve, reject) => {
            const client = new net.Socket();
            let buffer = '';
            console.log('connecting..')
            client.connect(this.port, this.host, () => {
                // Send payload with a newline delimiter as expected by Workerman Text protocol
                client.write(JSON.stringify(payload) + "\n");
            });

            client.on('data', (data) => {
                buffer += data.toString();
                // Check if we have a complete JSON object (simple heuristic for this use case)
                // In production, you might want strict framing, but Workerman usually sends in one go for small payloads.
                try {
                    const response = JSON.parse(buffer);
                    client.destroy(); // Close connection after response
                    resolve(response);
                } catch (e) {
                    console.log(e)
                    // Wait for more data if JSON is incomplete
                }
            });

            client.on('error', (err) => {
                client.destroy();
                reject(err);
            });

            client.on('close', () => {
                // If connection closes without resolving (and no error), handle strictly if needed
            });
        });
    }
}

const phpClient = new PHPWorkerClient();

async function main() {
    const data = {
        "gameId": "FlowersNET",
        "postData": {
            "action": "spin",
            "slotEvent": "bet",
            "bet_denomination": 5,
            "bet_betlevel": 1
        },
        "state": {
            "goldsvetData": {
                "paytable": {
                    "SYM_1": [0, 0, 5, 25, 100],
                    "SYM_2": [0, 0, 10, 50, 200],
                    "SYM_3": [0, 0, 15, 75, 300],
                    "SYM_4": [0, 0, 20, 100, 400],
                    "SYM_5": [0, 0, 25, 125, 500],
                    "SYM_6": [0, 0, 30, 150, 600],
                    "SYM_7": [0, 0, 35, 175, 700],
                    "SYM_8": [0, 0, 40, 200, 800],
                    "SYM_9": [0, 0, 45, 225, 900],
                    "SYM_10": [0, 0, 50, 250, 1000],
                    "SYM_11": [0, 0, 100, 500, 2000],
                    "SYM_12": [0, 0, 200, 1000, 5000]
                },
                "symbol_game": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                "denomination": "1,2,5,10,20,50,100"
            }
        },

        // COMPLETE USER OBJECT (Model/User.php structure)
        "user": {
            "id": 12345,
            "balance": 1000,
            "shop_id": 1,
            "count_balance": 950,
            "address": 50.00,
            "session": "session_string_here",
            "is_blocked": false,
            "status": "active",
            "remember_token": "remember_token_here",
            "last_bid": "2024-01-01 10:00:00"
        },

        // COMPLETE GAME OBJECT (Model/Game.php structure)
        "game": {
            "id": 1,
            "name": "FlowersNET",
            "shop_id": 1,
            "stat_in": 10000.00,
            "stat_out": 9500.00,
            "bids": 1000,
            "denomination": 1.0,
            "slotViewState": "Normal",
            "bet": "0.1,0.2,0.5,1,2,5",
            "jp_config": {
                "main_bank": 5000.00,
                "bonus_bank": 1000.00,
                "jp_1": 100.00,
                "jp_1_percent": 1.0,
                "lines_percent_config": {
                    "spin": {
                        "line10": { "0_100": 20 },
                        "line9": { "0_100": 25 },
                        "line5": { "0_100": 30 }
                    },
                    "bonus": {
                        "line10": { "0_100": 50 },
                        "line9": { "0_100": 60 },
                        "line5": { "0_100": 70 }
                    }
                }
            },
            "rezerv": 100,
            "view": true,
            "advanced": ""
        },

        // COMPLETE SHOP OBJECT (Model/Shop.php structure)
        "shop": {
            "id": 1,
            "max_win": 10000.00,
            "percent": 90.0,
            "is_blocked": false,
            "currency": "USD"
        },

        // COMPLETE JPG/JACKPOT OBJECTS (Model/JPG.php structure)
        "jpgs": [
            {
                "id": 1,
                "shop_id": 1,
                "balance": 1000.00,
                "percent": 1.0,
                "user_id": null,
                "start_balance": 1000.00
            },
            {
                "id": 2,
                "shop_id": 1,
                "balance": 2000.00,
                "percent": 2.0,
                "user_id": null,
                "start_balance": 2000.00
            },
            {
                "id": 3,
                "shop_id": 1,
                "balance": 5000.00,
                "percent": 3.0,
                "user_id": null,
                "start_balance": 5000.00
            }
        ],

        // GAME DATA STORAGE
        "gameData": {
            "free_spins": 0,
            "bonus_game": false,
            "last_win": 0
        },

        // PERSISTENT GAME DATA
        "gameDataStatic": {
            "SpinWinLimit": 0,
            "RtpControlCount": 200
        },

        // BANKING AND LOGGING
        "bankerService": null,
        "betLogs": [],

        // SLOT IDENTIFICATION
        "slotId": "TheWolfsBaneNET_001",
        "playerId": 12345,
        "balance": 1000.00,
        "jackpots": {
            "jackPay": 0,
            "jackpot1": 1000.00,
            "jackpot2": 2000.00,
            "jackpot3": 5000.00
        },

        // COMPLETE REEL STRIPS DATA (from reels.txt)
        "reelStrips": {
            "base": {
                "reelStrip1": [9, 8, 4, 8, 7, 10, 1, 6, 4, 2, 7, 8, 10, 4, 1, 5, 8, 10, 3, 5, 8, 0, 10, 3, 1, 5, 6, 2, 4, 9, 10, 6, 1, 4, 7, 9, 10, 4, 8, 5, 4, 8, 1, 3, 10, 5, 8, 6, 10, 7, 1, 5, 4, 2, 9, 4, 6, 10, 1, 7, 6, 3, 9, 7, 6, 3, 4, 1, 8, 6, 5, 3, 2, 10, 5, 3, 9, 1, 5, 4, 8, 7, 3, 6, 5, 4, 1, 9, 3, 10, 8, 0, 3, 6, 7, 1, 8, 3, 5, 4, 3, 10, 8, 1, 7, 5, 9, 3, 10, 6, 5, 8, 1, 4, 3, 5, 6],
                "reelStrip2": [9, 0, 6, 1, 7, 9, 4, 5, 8, 7, 6, 1, 4, 3, 9, 6, 9, 7, 8, 9, 7, 6, 1, 9, 10, 7, 0, 6, 9, 7, 1, 5, 9, 4, 7, 9, 3, 5, 7, 3, 4, 1, 9, 6, 7, 3, 9, 5, 8, 7, 5, 6, 1, 7, 9, 5, 7, 3, 0, 5, 9, 7, 1, 6, 9, 4, 7, 6, 5, 9, 1, 6, 4, 7, 10, 9, 7, 4, 9, 10, 1, 7],
                "reelStrip3": [3, 10, 8, 0, 9, 8, 1, 10, 6, 2, 5, 3, 8, 10, 5, 8, 0, 1, 4, 3, 8, 4, 10, 2, 7, 8, 1, 10, 4, 3, 5, 10, 8, 5, 6, 1, 0, 5, 3, 2, 4, 5, 8, 1, 10, 2, 8, 9, 7, 8, 5, 10, 9, 1, 4, 8, 3, 0, 10, 6, 8, 3, 4, 10, 5, 1, 6, 4, 10, 7, 5, 0, 8, 7, 3, 10, 1, 9, 6, 8, 10, 5, 0, 6, 4, 1, 8, 6, 2, 3, 10, 8, 4, 1, 5, 9, 8, 6, 7, 10, 8, 6, 5, 2, 10, 1, 8, 9, 3, 4, 8, 7, 10, 2, 6, 8, 1, 9, 7, 3, 0, 10, 8, 4, 1, 6, 4, 8, 6, 3, 10, 7, 8],
                "reelStrip4": [10, 9, 1, 6, 3, 5, 8, 9, 10, 6, 7, 4, 1, 9, 7, 8, 10, 7, 9, 3, 8, 7, 1, 0, 9, 7, 8, 9, 7, 6, 3, 8, 9, 7, 1, 6, 10, 9, 7, 5, 8, 6, 5, 9, 4, 8, 10, 1, 3, 7, 8, 6, 9, 0, 4, 9, 1, 10, 6, 7, 8, 10, 6, 9, 10, 1, 4, 7, 6, 8, 10, 5, 3, 8, 1, 5, 4, 7, 9, 3, 10, 5, 1, 6, 8, 9, 7, 6, 4, 10, 6, 0, 8, 10, 1, 4, 6, 3, 7, 8, 6, 10, 1, 9, 8, 10, 5, 7],
                "reelStrip5": [7, 0, 9, 7, 1, 6, 10, 7, 5, 6, 8, 7, 2, 9, 1, 6, 2, 9, 10, 8, 7, 10, 1, 9, 4, 8, 7, 2, 9, 3, 8, 10, 5, 3, 1, 6, 9, 10, 7, 3, 2, 8, 10, 9, 6, 1, 10, 4, 8, 6, 4, 10, 5, 8, 9, 8, 7, 1, 10, 6],
                "reelStrip6": []
            },
            "bonus": {
                "reelStrip1": [4, 7, 9, 10, 6, 4, 0, 10, 4, 11, 8, 9, 5, 3, 6, 5, 7, 3, 11, 9, 6, 4, 5, 8, 7, 10, 3, 11, 6, 9, 7, 5, 9, 3, 7, 6, 4, 11, 5, 3, 8, 10, 3, 8, 4, 11, 6, 5, 10, 8],
                "reelStrip2": [10, 9, 5, 11, 6, 9, 7, 10, 3, 6, 9, 7, 10, 8, 11, 4, 9, 7, 4, 10, 6, 7, 8, 10, 11, 0, 8, 9, 4, 10, 7, 6, 11, 8, 4, 5, 9, 6, 10, 3, 11, 5, 7, 9, 10, 5, 7, 4, 11, 6, 7, 9, 8, 10, 4, 6, 5, 11, 10, 9, 7, 8, 9, 10, 3, 11, 9, 8, 7, 5, 10, 9, 7, 10, 11, 8, 9, 0, 10, 7, 5, 4, 7, 3, 11, 9, 8, 10, 7, 9, 6, 7, 9, 5, 11, 10, 8, 7],
                "reelStrip3": [10, 6, 8, 4, 7, 3, 11, 10, 5, 7, 8, 6, 4, 8, 11, 3, 4, 7, 9, 5, 10, 4, 11, 6, 9, 8, 7, 3, 0, 9, 6, 8, 5, 11, 10, 6, 9, 7, 9, 4, 5, 10, 8, 4, 7, 11, 6, 10, 3, 8, 10, 9, 4, 3, 10, 7, 11, 8, 6, 9, 5, 3, 9, 10, 11, 7, 8, 10, 4, 9, 7, 6, 7, 11, 8, 9, 10, 5, 4, 8, 7, 3, 5, 9, 10, 11, 4, 3, 6, 7, 8, 3, 10, 4, 6, 7, 5, 11, 9, 8, 5, 7, 3, 9, 10, 8, 6, 7, 11, 5, 8, 7, 9, 5, 10, 8, 5, 0, 7, 10, 11, 9, 4, 7, 3, 9, 8, 10, 11, 3, 6, 8, 9],
                "reelStrip4": [10, 3, 7, 9, 8, 11, 6, 10, 3, 5, 7, 9, 4, 8, 9, 7, 11, 6, 5, 0, 4, 10, 9, 6, 11, 4, 7, 3, 10, 8, 0, 7, 9, 8, 7, 11, 3, 10, 6, 4, 5, 10, 7, 8, 11, 9, 6, 7, 10, 4, 9, 8, 6, 7, 11, 3, 10, 4, 6, 5, 0, 9, 8, 11, 10, 7, 9, 6, 7, 3, 6, 9, 8, 10, 11, 5, 9, 8, 7, 5, 6, 10, 3, 8, 9, 8, 11, 4, 6, 5, 10, 8, 6],
                "reelStrip5": [0, 7, 9, 8, 6, 10, 4, 11, 9, 8, 6, 3, 8, 10, 9, 7, 11, 8, 10, 9, 6, 8, 9, 7, 10, 9, 6, 4, 11, 3, 6, 10, 9, 6, 7, 8, 10, 11, 5, 9, 4, 8, 3, 9, 7, 8, 10, 6, 11, 7, 10, 5, 8, 9, 10, 6, 5, 0, 8, 7, 6, 10, 11, 8, 5, 9, 10, 6, 7, 8, 9, 6, 10, 7, 11, 0, 10, 9, 8, 7, 9, 10, 4, 11, 7, 8, 9, 7, 8, 10, 3, 6, 11, 9, 10, 8],
                "reelStrip6": []
            }
        }
    }

    console.log('executing..')
    const phpResponse = await phpClient.execute(data);
    console.log(phpResponse)
    // 6. Error Handling from PHP
    if (phpResponse.responseEvent === 'error') {
        console.error("PHP Logic Error:", phpResponse);
        return phpResponse.response; // Return the error string to client
    }

}


await main()
