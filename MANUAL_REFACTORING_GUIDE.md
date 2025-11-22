# Manual SlotSettings Refactoring Guide

## Overview
This guide provides step-by-step instructions for manually refactoring individual SlotSettings.php files to extend BaseSlotSettings and remove duplicated methods.

## Files to Refactor (Skip StarBurstNET and HalloweenJackNET)
- CreatureFromTheBlackLagoonNET
- DazzleMeNET
- FlowersChristmasNET
- FlowersNET
- FortuneRangersNET
- FruitShopChristmasNET
- FruitShopNET
- GoBananasNET
- GoldenGrimoireNET
- GrandSpinnSuperpotNET
- JumanjiNET
- LightsNET
- ReelRush2NET
- SantaVSRudolphNET
- SpaceWarsNET
- TheWolfsBaneNET
- TurnYourFortuneNET
- VikingsNET
- WildWaterNET
- WingsOfRichesNET

## Step-by-Step Refactoring Process

### Step 1: Create Backup
```bash
cp "GameNameNET/SlotSettings.php" ".slotsettings_backups/GameName_SlotSettings.php.backup"
```

### Step 2: Modify Class Declaration
**Find:**
```php
class SlotSettings
```

**Replace with:**
```php
class SlotSettings extends \Games\BaseSlotSettings
```

### Step 3: Remove Shared Methods
Remove these methods completely (they exist in BaseSlotSettings):

1. is_active()
2. SetGameData($key, $value)
3. GetGameData($key)
4. HasGameData($key)
5. SaveGameData()
6. SetGameDataStatic($key, $value)
7. GetGameDataStatic($key)
8. HasGameDataStatic($key)
9. SaveGameDataStatic()
10. GetBank($slotState = '')
11. SetBank($slotState = '', $sum, $slotEvent = '')
12. GetBalance()
13. SetBalance($sum, $slotEvent = '')
14. GetPercent()
15. GetCountBalanceUser()
16. UpdateJackpots($bet)
17. FormatFloat($num)
18. CheckBonusWin()
19. GetRandomPay()
20. InternalError($errcode)
21. InternalErrorSilent($errcode)
22. SaveLogReport($response, $allbet, $lines, $reportWin, $slotEvent)
23. GetHistory()
24. GetGambleSettings()

### Step 4: Keep Game-Specific Methods
- __construct() (simplified)
- GetSpinSettings($garantType, $bet, $lines)
- getNewSpin($game, $spinWin, $bonusWin, $lines, $garantType)
- GetRandomScatterPos($rp)
- GetReelStrips($winType, $slotEvent)
- Any other game-specific methods

## Success Criteria
- Class properly extends BaseSlotSettings
- All shared methods removed
- Game-specific methods preserved
- PHP syntax validation passes