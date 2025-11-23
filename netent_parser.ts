// --- 1. The Types ---

// Represents a single Reel (e.g., Reel 1)
export interface SlotReel {
    symbols?: string[];       // "syms" -> "symbols" (e.g., ["SYM1", "SYM2"])
    stopPosition?: number;    // "pos"  -> "stopPosition"
    isHeld?: boolean;         // "hold" -> "isHeld"
    attention?: {             // Special animation triggers
        [index: string]: number;
    };
    // Allow for other dynamic reel properties
    [key: string]: any;
}

// Represents a Reel Set (e.g., Basic Game vs Free Spin)
export interface SlotReelSet {
    id: string;               // e.g., "basic", "freespin"
    reels: {
        [reelIndex: string]: SlotReel;
    };
    [key: string]: any;
}

// Represents a Bet Line (Payline) configuration
export interface SlotBetLine {
    id?: number;
    coins?: number;
    line?: number[];         // The pattern of the line (e.g., [1,2,1,2,1])
    reelset?: string;        // Which reel set applies to this line
    [key: string]: any;
}

// The Main State Object

export interface SlotGameState {
    // --- Renamed / Mapped Keys ---
    reelSets?: {
        [setIndex: string]: SlotReelSet;
    };
    betLines?: {
        [lineIndex: string]: SlotBetLine;
    };

    // --- Standard Keys (CamelCase or Mapped) ---
    action?: string;          // "clientaction"
    nextAction?: string;      // "nextaction"
    currency?: string;        // "playercurrencyiso"

    // --- Stats & Money ---
    gameStats?: {             // "game"
        win?: {
            coins: number;
            cents: number;
            amount: number;
        };
    };
    totalWin?: {
        coins: number;
        cents: number;
    };
    credit?: number;

    // --- Game State Flags ---
    gameOver?: boolean;       // "gameover"
    isJackpotWin?: boolean;
    waveCount?: number;
    multiplier?: number;

    // --- Catch-all for other dynamic keys ---
    [key: string]: any;
}

// --- 2. The Updated Function with Types ---

const KEY_MAP: { [key: string]: string } = {
    "rs": "reelSets",
    "bl": "betLines",
    "r": "reels",
    "syms": "symbols",
    "pos": "stopPosition",
    "hold": "isHeld",
    "clientaction": "action",
    "playercurrencyiso": "currency",
    "game": "gameStats"
};

export default function parseSlotString(inputString: string): SlotGameState {
    const params = new URLSearchParams(inputString);
    const root: any = {};

    params.forEach((rawValue, fullKey) => {
        // Value conversion
        let value: any = rawValue;
        if (value === 'true') value = true;
        else if (value === 'false') value = false;
        else if (!isNaN(Number(value)) && value.trim() !== '') value = Number(value);
        else if (value.includes(',') && !value.startsWith('http')) {
            value = value.split(',').map((item: string) =>
                !isNaN(Number(item)) ? Number(item) : item
            );
        }

        // Key translation
        const parts = fullKey.split('.');
        let currentLevel = root;

        for (let i = 0; i < parts.length; i++) {
            let part = parts[i];

            // Map keys
            if (KEY_MAP[part]) part = KEY_MAP[part];

            // Normalize indices (remove 'i' prefix)
            if (/^i\d+$/.test(part)) part = part.substring(1);

            if (i === parts.length - 1) {
                currentLevel[part] = value;
            } else {
                if (!currentLevel[part]) currentLevel[part] = {};
                currentLevel = currentLevel[part];
            }
        }
    });

    return root as SlotGameState;
}
// 1. Define the Semantic Dictionary based on our analysis
// const KEY_MAP: { [key: string]: string } = {
//     // Top Level Structures
//     "rs": "reelSets",       // rs = Reel Sets (Game modes)
//     "bl": "betLines",       // bl = Bet Lines (Paylines)

//     // Inner Structures
//     "r": "reels",          // r  = Reels (The columns)
//     "syms": "symbols",      // syms = Symbol IDs shown
//     "pos": "stopPosition",  // pos = Index on the reel strip
//     "hold": "isHeld",       // hold = Is reel held (respin)

//     // Metadata
//     "clientaction": "action",
//     "playercurrencyiso": "currency",
//     "game": "gameStats"
// };

interface SlotData {
    [key: string]: any;
}

/**
 * Parses the NetEnt init string and translates cryptic keys
 * into human-readable semantic names.
 */
// function parseAndTranslateSlotData(inputString: string): SlotData {
//     const params = new URLSearchParams(inputString);
//     const root: SlotData = {};

//     params.forEach((rawValue, fullKey) => {
//         // --- Step A: Value Type Conversion (Same as before) ---
//         let value: any = rawValue;
//         if (value === 'true') value = true;
//         else if (value === 'false') value = false;
//         else if (!isNaN(Number(value)) && value.trim() !== '') value = Number(value);
//         else if (value.includes(',') && !value.startsWith('http')) {
//             value = value.split(',').map((item: string) =>
//                 !isNaN(Number(item)) ? Number(item) : item
//             );
//         }

//         // --- Step B: Key Translation & Unflattening ---
//         // 1. Split the key by dot (e.g. "rs.i0.r.i4.pos")
//         const parts = fullKey.split('.');

//         let currentLevel = root;

//         for (let i = 0; i < parts.length; i++) {
//             let part = parts[i];

//             // 2. TRANSLATION LOGIC
//             // If the part is in our dictionary, rename it (e.g., "pos" -> "stopPosition")
//             if (KEY_MAP[part]) {
//                 part = KEY_MAP[part];
//             }

//             // 3. INDEX LOGIC (Handling i0, i1, i15)
//             // If part starts with 'i' followed by a number, strip the 'i' to look like a real index
//             // e.g., "i0" becomes "0", "i15" becomes "15"
//             if (/^i\d+$/.test(part)) {
//                 part = part.substring(1);
//             }

//             // 4. Build the nested object
//             const isLastPart = i === parts.length - 1;

//             if (isLastPart) {
//                 currentLevel[part] = value;
//             } else {
//                 // If this key doesn't exist yet, create an object
//                 // Note: We use objects instead of Arrays because indices might be sparse or non-sequential
//                 if (!currentLevel[part]) {
//                     currentLevel[part] = {};
//                 }
//                 currentLevel = currentLevel[part];
//             }
//         }
//     });

//     return root;
// }

// --- Usage Example with your Init String ---
// const rawInitString = "rs.i1.r.i0.syms=SYM1%2CSYM1%2CSYM1&bl.i6.coins=1&rs.i8.r.i3.hold=false&bl.i17.reelset=ALL&bl.i15.id=15&rs.i0.r.i4.hold=false&rs.i9.r.i1.hold=false&rs.i1.r.i2.hold=false&rs.i8.r.i1.syms=SYM3%2CSYM9%2CSYM9&game.win.cents=0&rs.i7.r.i3.syms=SYM7%2CSYM6%2CSYM8&staticsharedurl=https%3A%2F%2Fstatic-shared.casinomodule.com%2Fgameclient_html%2Fdevicedetection%2Fcurrent&bl.i10.line=1%2C2%2C1%2C2%2C1&bl.i0.reelset=ALL&bl.i18.coins=1&bl.i10.id=10&bl.i3.reelset=ALL&bl.i4.line=2%2C1%2C0%2C1%2C2&bl.i13.coins=1&rs.i2.r.i0.hold=false&rs.i0.r.i0.syms=SYM7%2CSYM4%2CSYM7&rs.i9.r.i3.hold=false&bl.i2.id=2&rs.i1.r.i1.pos=1&rs.i7.r.i1.syms=SYM7%2CSYM7%2CSYM6&rs.i3.r.i4.pos=0&rs.i6.r.i3.syms=SYM5%2CSYM4%2CSYM8&rs.i0.r.i0.pos=0&bl.i14.reelset=ALL&rs.i2.r.i3.pos=0&rs.i5.r.i0.pos=0&rs.i7.id=basic&rs.i7.r.i3.pos=2&rs.i2.r.i4.hold=false&rs.i3.r.i1.pos=0&rs.i2.id=freespinlevel1&rs.i6.r.i1.pos=0&game.win.coins=0&rs.i1.r.i0.hold=false&bl.i3.id=3&bl.i12.coins=1&bl.i8.reelset=ALL&clientaction=init&rs.i4.r.i0.hold=false&rs.i0.r.i2.hold=false&rs.i4.r.i3.syms=SYM5%2CSYM4%2CSYM8&bl.i16.id=16&casinoID=netent&bl.i5.coins=1&rs.i3.r.i2.hold=false&bl.i8.id=8&rs.i5.r.i1.syms=SYM3%2CSYM9%2CSYM9&rs.i7.r.i0.pos=10&rs.i7.r.i3.hold=false&rs.i0.r.i3.pos=0&rs.i4.r.i0.syms=SYM7%2CSYM4%2CSYM7&rs.i8.r.i1.pos=0&rs.i5.r.i3.pos=0&bl.i6.line=2%2C2%2C1%2C2%2C2&bl.i12.line=2%2C1%2C2%2C1%2C2&bl.i0.line=1%2C1%2C1%2C1%2C1&rs.i4.r.i2.pos=0&rs.i0.r.i2.syms=SYM8%2CSYM8%2CSYM4&rs.i8.r.i1.hold=false&rs.i9.r.i2.pos=0&game.win.amount=0&betlevel.all=1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%2C10&rs.i5.r.i2.hold=false&denomination.all=100%2C200%2C500%2C1000%2C2000%2C5000%2C10000&rs.i2.r.i0.pos=0&current.rs.i0=basic&rs.i7.r.i2.pos=19&bl.i1.id=1&rs.i3.r.i2.syms=SYM8%2CSYM8%2CSYM4&rs.i1.r.i4.pos=10&rs.i8.id=freespinlevel3&denomination.standard=100&rs.i3.id=freespinlevel0respin&multiplier=1&bl.i14.id=14&bl.i19.line=0%2C2%2C2%2C2%2C0&bl.i12.reelset=ALL&bl.i2.coins=1&bl.i6.id=6&autoplay=10%2C25%2C50%2C75%2C100%2C250%2C500%2C750%2C1000&rs.i6.r.i2.pos=0&rs.i1.r.i4.syms=SYM9%2CSYM9%2CSYM5&gamesoundurl=https%3A%2F%2Fstatic.casinomodule.com%2F&rs.i5.r.i2.syms=SYM10%2CSYM10%2CSYM5&rs.i5.r.i3.hold=false&rs.i4.r.i2.hold=false&bl.i5.reelset=ALL&rs.i4.r.i1.syms=SYM7%2CSYM7%2CSYM3&bl.i19.coins=1&bl.i7.id=7&bl.i18.reelset=ALL&rs.i2.r.i4.pos=0&rs.i3.r.i0.syms=SYM4%2CSYM7%2CSYM7&rs.i8.r.i4.pos=0&playercurrencyiso=USD&bl.i1.coins=1&rs.i4.r.i1.hold=false&rs.i3.r.i2.pos=0&bl.i14.line=1%2C1%2C2%2C1%2C1&playforfun=false&rs.i8.r.i0.hold=false&jackpotcurrencyiso=USD&rs.i0.r.i4.syms=SYM6%2CSYM10%2CSYM9&rs.i0.r.i2.pos=0&bl.i13.line=1%2C1%2C0%2C1%2C1&rs.i6.r.i3.pos=0&rs.i1.r.i0.pos=10&rs.i6.r.i3.hold=false&bl.i0.coins=1&rs.i2.r.i0.syms=SYM7%2CSYM4%2CSYM7&bl.i2.reelset=ALL&rs.i3.r.i1.syms=SYM7%2CSYM7%2CSYM3&rs.i1.r.i4.hold=false&rs.i9.r.i3.pos=0&rs.i4.r.i1.pos=0&rs.i4.r.i2.syms=SYM8%2CSYM8%2CSYM4&bl.standard=0%2C1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%2C10%2C11%2C12%2C13%2C14%2C15%2C16%2C17%2C18%2C19&rs.i5.r.i3.syms=SYM6%2CSYM7%2CSYM7&rs.i3.r.i0.hold=false&rs.i9.r.i1.syms=SYM3%2CSYM9%2CSYM9&rs.i6.r.i4.syms=SYM6%2CSYM10%2CSYM4&rs.i8.r.i0.syms=SYM7%2CSYM4%2CSYM7&rs.i8.r.i0.pos=0&bl.i15.reelset=ALL&rs.i0.r.i3.hold=false&rs.i5.r.i4.pos=0&rs.i9.id=freespinlevel2&rs.i4.id=freespinlevel3respin&rs.i7.r.i2.syms=SYM8%2CSYM4%2CSYM3&rs.i2.r.i1.hold=false&gameServerVersion=1.5.0&g4mode=false&bl.i11.line=0%2C1%2C0%2C1%2C0&historybutton=false&bl.i5.id=5&gameEventSetters.enabled=false&next.rs=basic&rs.i1.r.i3.pos=2&rs.i0.r.i1.syms=SYM7%2CSYM7%2CSYM3&bl.i3.coins=1&bl.i10.coins=1&bl.i18.id=18&rs.i2.r.i1.pos=0&rs.i7.r.i4.hold=false&rs.i4.r.i4.pos=0&rs.i8.r.i2.hold=false&rs.i1.r.i3.hold=false&rs.i7.r.i1.pos=1&totalwin.coins=0&rs.i5.r.i4.syms=SYM6%2CSYM9%2CSYM9&rs.i9.r.i4.pos=0&bl.i5.line=0%2C0%2C1%2C0%2C0&gamestate.current=basic&rs.i4.r.i0.pos=0&jackpotcurrency=%26%23x20AC%3B&bl.i7.line=1%2C2%2C2%2C2%2C1&rs.i8.r.i2.syms=SYM10%2CSYM10%2CSYM5&rs.i9.r.i0.hold=false&rs.i3.r.i1.hold=false&rs.i9.r.i0.syms=SYM7%2CSYM4%2CSYM7&rs.i7.r.i4.syms=SYM0%2CSYM9%2CSYM9&rs.i0.r.i3.syms=SYM5%2CSYM4%2CSYM8&rs.i1.r.i1.syms=SYM7%2CSYM7%2CSYM6&bl.i16.coins=1&bl.i9.coins=1&bl.i7.reelset=ALL&isJackpotWin=false&rs.i6.r.i4.hold=false&rs.i2.r.i3.hold=false&rs.i0.r.i1.pos=0&rs.i4.r.i4.syms=SYM6%2CSYM10%2CSYM9&rs.i1.r.i3.syms=SYM7%2CSYM6%2CSYM8&bl.i13.id=13&rs.i0.r.i1.hold=false&rs.i2.r.i1.syms=SYM3%2CSYM9%2CSYM9&rs.i9.r.i2.syms=SYM10%2CSYM10%2CSYM5&bl.i9.line=1%2C0%2C1%2C0%2C1&rs.i8.r.i4.syms=SYM6%2CSYM9%2CSYM9&rs.i9.r.i0.pos=0&rs.i8.r.i3.pos=0&betlevel.standard=1&bl.i10.reelset=ALL&rs.i6.r.i2.syms=SYM8%2CSYM6%2CSYM4&rs.i7.r.i0.syms=SYM6%2CSYM3%2CSYM9&gameover=true&rs.i3.r.i3.pos=0&rs.i5.id=freespinlevel0&rs.i7.r.i0.hold=false&rs.i6.r.i4.pos=0&bl.i11.coins=1&rs.i5.r.i1.hold=false&rs.i5.r.i4.hold=false&rs.i6.r.i2.hold=false&bl.i13.reelset=ALL&bl.i0.id=0&rs.i9.r.i2.hold=false&nextaction=spin&bl.i15.line=0%2C1%2C1%2C1%2C0&bl.i3.line=0%2C1%2C2%2C1%2C0&bl.i19.id=19&bl.i4.reelset=ALL&bl.i4.coins=1&bl.i18.line=2%2C0%2C2%2C0%2C2&rs.i8.r.i4.hold=false&bl.i9.id=9&bl.i17.line=0%2C2%2C0%2C2%2C0&bl.i11.id=11&rs.i4.r.i3.pos=0&playercurrency=%26%23x20AC%3B&bl.i9.reelset=ALL&rs.i4.r.i4.hold=false&bl.i17.coins=1&rs.i5.r.i0.syms=SYM7%2CSYM4%2CSYM7&bl.i19.reelset=ALL&rs.i2.r.i4.syms=SYM6%2CSYM9%2CSYM9&rs.i7.r.i4.pos=10&rs.i4.r.i3.hold=false&rs.i6.r.i0.hold=false&bl.i11.reelset=ALL&bl.i16.line=2%2C1%2C1%2C1%2C2&rs.i0.id=freespinlevel2respin&credit=100000&rs.i9.r.i3.syms=SYM6%2CSYM7%2CSYM7&bl.i1.reelset=ALL&rs.i2.r.i2.pos=0&rs.i5.r.i1.pos=0&bl.i1.line=0%2C0%2C0%2C0%2C0&rs.i6.r.i0.syms=SYM7%2CSYM4%2CSYM7&rs.i6.r.i1.hold=false&bl.i17.id=17&rs.i2.r.i2.syms=SYM10%2CSYM10%2CSYM5&rs.i1.r.i2.pos=19&bl.i16.reelset=ALL&rs.i3.r.i3.syms=SYM6%2CSYM7%2CSYM7&rs.i3.r.i4.hold=false&rs.i5.r.i0.hold=false&nearwinallowed=true&rs.i9.r.i1.pos=0&bl.i8.line=1%2C0%2C0%2C0%2C1&rs.i7.r.i2.hold=false&rs.i6.r.i1.syms=SYM5%2CSYM9%2CSYM9&rs.i3.r.i3.hold=false&rs.i6.r.i0.pos=0&bl.i8.coins=1&bl.i15.coins=1&bl.i2.line=2%2C2%2C2%2C2%2C2&rs.i1.r.i2.syms=SYM8%2CSYM4%2CSYM3&rs.i9.r.i4.hold=false&rs.i6.id=freespinlevel1respin&totalwin.cents=0&rs.i7.r.i1.hold=false&rs.i5.r.i2.pos=0&rs.i0.r.i0.hold=false&rs.i2.r.i3.syms=SYM6%2CSYM7%2CSYM7&rs.i8.r.i2.pos=0&restore=false&rs.i1.id=basicrespin&rs.i3.r.i4.syms=SYM6%2CSYM9%2CSYM4&bl.i12.id=12&bl.i4.id=4&rs.i0.r.i4.pos=0&bl.i7.coins=1&bl.i6.reelset=ALL&rs.i3.r.i0.pos=0&rs.i2.r.i2.hold=false&wavecount=1&rs.i9.r.i4.syms=SYM6%2CSYM9%2CSYM9&bl.i14.coins=1&rs.i8.r.i3.syms=SYM6%2CSYM7%2CSYM7&rs.i1.r.i1.hold=false&rs.i0.r.i0.syms=SYM2%2CSYM7%2CSYM1%2CSYM3&rs.i0.r.i1.syms=SYM6%2CSYM2%2CSYM3%2CSYM2&rs.i0.r.i2.syms=SYM5%2CSYM3%2CSYM6%2CSYM2&rs.i0.r.i3.syms=SYM3%2CSYM3%2CSYM7%2CSYM3&rs.i0.r.i4.syms=SYM2%2CSYM7%2CSYM6%2CSYM4&rs.i0.r.i0.pos=7&rs.i0.r.i1.pos=10&rs.i0.r.i2.pos=2&rs.i0.r.i3.pos=5&rs.i0.r.i4.pos=5&rs.i1.r.i0.pos=5&rs.i1.r.i1.pos=3&rs.i1.r.i2.pos=3&rs.i1.r.i3.pos=6&rs.i1.r.i4.pos=4";

// export default parseSlotString();

// Log to show the friendly structure
// console.log(JSON.stringify(result, null, 2));

// Example of accessing the new structure
// Old way: result.rs.i0.r.i0.pos
// New way: result.reelSets['0'].reels['0'].stopPosition