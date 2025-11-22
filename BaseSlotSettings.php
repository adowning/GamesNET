<?php
// BaseSlotSettings.php (Stateless Version)
namespace Games;

use Games\Log;

class BaseSlotSettings
{
    // protected UnifiedReelManager $reelManager;
    public $splitScreen = null;
    public $reelStrip1 = null;
    public $reelStrip2 = null;
    public $reelStrip3 = null;
    public $reelStrip4 = null;
    public $reelStrip5 = null;
    public $reelStrip6 = null;
    public $reelStripBonus1 = null;
    public $reelStripBonus2 = null;
    public $reelStripBonus3 = null;
    public $reelStripBonus4 = null;
    public $reelStripBonus5 = null;
    public $reelStripBonus6 = null;
    public $slotDBId = '';
    public $Line = null;
    public $scaleMode = null;
    public $numFloat = null;
    public $gameLine = null;
    public $Bet = null;
    public $SymbolGame = null;
    public $GambleType = null;
    public $lastEvent = null;
    public $keyController = null;
    public $slotViewState = null;
    public $hideButtons = null;
    public $slotReelsConfig = null;
    public $slotFreeCount = null;
    public $slotExitUrl = null;
    public $slotBonusType = null;
    public $slotScatterType = null;
    public $slotGamble = null;
    public $slotSounds = [];
    public $jpgs = null;
    public $betLogs;
    public $increaseRTP = null;

    protected $betRemains = null;
    protected $betRemains0 = null;
    public $toGameBanks = null;
    public $toSlotJackBanks = null;
    public $toSysJackBanks = null;
    public $betProfit = null;
    public $slotJackPercent = [];
    public $slotJackpot = [];

    // Dependencies
    protected $bankerService;
    protected $userStatusEnum;

    public $logReport = [];
    public $internalError = [];
    public $gameData = [];
    public $gameDataStatic = [];
    public $AllBet = 0;
    public $MaxWin = 0;
    public $CurrentDenom = 1;
    public $GameData = [];
    public $Denominations = [];
    public $CurrentDenomination = 1.0;
    public $slotCurrency = 'USD';
    public $playerId = null;
    public $Balance = null;
    public $Jackpots = [];
    public $Paytable = [];
    public $slotId = '';
    public $Bank = null;
    public $Percent = null;
    public $WinLine = null;
    public $WinGamble = null;
    public $Bonus = null;
    public $shop_id = null;
    public $currency = null;
    public $user = null;
    public $game = null;
    public $shop = null;
    public $jpgPercentZero = false;
    public $count_balance = null;

    // Game State Properties
    public $slotBonus = null;
    public $isBonusStart = null;
    public $slotFreeMpl = 1;
    public $slotWildMpl = 1;

    public $goldsvetData;

    // Configuration Defaults
    public $reelRows = 3;
    public $scatterSymbolId = '0';

    // Reel Strip Placeholders
    public $reelStripsData = [];

    // -------------------------------------------------------
    // CONSTRUCTOR
    // -------------------------------------------------------
    public $slotFastStop;
    public function __construct($settings)
    {

        // 1. Unpack settings
        $this->user = $settings['user'] ?? null;
        $this->game = $settings['game'] ?? null;
        $this->shop = $settings['shop'] ?? null;
        $this->jpgs = $settings['jpgs'] ?? [];
        $this->gameData = $settings['gameData'] ?? [];
        $this->gameDataStatic = $settings['gameDataStatic'] ?? [];
        $this->bankerService = $settings['bankerService'] ?? null;
        $this->betLogs = $settings['betLogs'] ?? null;

        $this->slotId = $settings['slotId'] ?? null;
        $this->playerId = $settings['playerId'] ?? null;
        $this->Balance = $settings['balance'] ?? 0;
        $this->Jackpots = $settings['jackpots'] ?? [];

        $this->goldsvetData = $settings['state']['goldsvetData'] ?? [];
        $this->Paytable = $settings['state']['goldsvetData']['paytable'] ?? [];
        $this->SymbolGame = $settings['state']['goldsvetData']['symbol_game'] ?? [];

        $this->userStatusEnum = 'BANNED';

        // 2. Load Reel Strips
        if (isset($settings['reelStrips']['base'])) {
            $this->reelStripsData = $settings['reelStrips'];
            foreach ($settings['reelStrips']['base'] as $key => $values) {
                if (property_exists($this, $key)) {
                    $this->$key = $values;
                }
            }
        }
        if (isset($settings['reelStrips']['bonus'])) {
            foreach ($settings['reelStrips']['bonus'] as $key => $values) {
                if (property_exists($this, $key)) {
                    $this->$key = $values;
                }
            }
        }

        // 3. Init Denominations
        if (isset($settings['state']['goldsvetData']['denomination'])) {
            $this->Denominations = explode(',', (string)$settings['state']['goldsvetData']['denomination']);
        } else {
            $this->Denominations = [1.0];
        }

        // 4. Safe Property Access
        $this->shop_id = is_array($this->shop) ? ($this->shop['id'] ?? 0) : ($this->shop->id ?? 0);

        if (!$this->playerId) {
            $this->playerId = is_array($this->user) ? ($this->user['id'] ?? 0) : ($this->user->id ?? 0);
        }

        // Fallback: If user is missing (e.g. passed only 'state'), create a temporary one from basic settings
        if ($this->user === null) {
            $this->user = [
                'id' => $this->playerId,
                'balance' => $this->Balance, // Use the balance passed in settings
                'count_balance' => $settings['count_balance'] ?? 0,
                'address' => 0 // Default address if needed
            ];
        }

        $this->slotDBId = is_array($this->game) ? ($this->game['id'] ?? 0) : ($this->game->id ?? 0);
        $this->count_balance = is_array($this->user) ? ($this->user['count_balance'] ?? 0) : ($this->user->count_balance ?? 0);
        $this->Percent = is_array($this->shop) ? ($this->shop['percent'] ?? 0) : ($this->shop->percent ?? 90);
        $this->WinGamble = is_array($this->game) ? ($this->game['rezerv'] ?? 0) : ($this->game->rezerv ?? 0);
        $this->MaxWin = is_array($this->shop) ? ($this->shop['max_win'] ?? 0) : ($this->shop->max_win ?? 0);

        // Denom Logic
        $gameDenoms = is_array($this->game) ? ($this->game['denominations'] ?? []) : ($this->game->denominations ?? []);
        if (empty($this->Denominations) && !empty($gameDenoms)) {
            $this->Denominations = $gameDenoms;
        }
        $this->CurrentDenom = $this->Denominations[0] ?? 1;
        $this->increaseRTP = 1;

        $this->slotCurrency = is_array($this->shop) ? ($this->shop['currency'] ?? 'USD') : ($this->shop->currency ?? 'USD');

        // Bank Logic
        if ($settings['bank'] ?? false) {
            $this->Bank = $settings['bank'];
        } else {
            $this->Bank = is_array($this->shop) ? ($this->shop['balance'] ?? 1000) : ($this->shop->balance ?? 1000);
        }

        $this->logReport = [];
        $this->internalError = [];
    }

    // -------------------------------------------------------
    // LOGIC METHODS
    // -------------------------------------------------------

    public function is_active()
    {
        $game_view = is_array($this->game) ? ($this->game['view'] ?? true) : ($this->game->view ?? true);
        $shop_blocked = is_array($this->shop) ? ($this->shop['is_blocked'] ?? false) : ($this->shop->is_blocked ?? false);
        $user_blocked = is_array($this->user) ? ($this->user['is_blocked'] ?? false) : ($this->user->is_blocked ?? false);
        $user_status = is_array($this->user) ? ($this->user['status'] ?? '') : ($this->user->status ?? '');

        if ($this->game && $this->shop && $this->user && (!$game_view || $shop_blocked || $user_blocked || $user_status == $this->userStatusEnum)) {
            if (!is_array($this->user)) {
                $this->user->remember_token = null;
            }
            return false;
        }
        return $game_view && !$shop_blocked && !$user_blocked && $user_status != $this->userStatusEnum;
    }

    public function SetGameData($key, $value)
    {
        $timeLife = 86400;
        $this->gameData[$key] = [
            'timelife' => time() + $timeLife,
            'payload' => $value
        ];
    }

    public function GetGameData($key)
    {
        if (isset($this->gameData[$key])) {
            return $this->gameData[$key]['payload'];
        } else {
            return 0;
        }
    }

    public function FormatFloat($num)
    {
        $str0 = explode('.', $num);
        if (isset($str0[1])) {
            if (strlen($str0[1]) > 4) {
                return round($num * 100) / 100;
            } else if (strlen($str0[1]) > 2) {
                return floor($num * 100) / 100;
            } else {
                return $num;
            }
        } else {
            return $num;
        }
    }

    public function SaveGameData()
    {
        $this->user->session = serialize($this->gameData);
        $this->user->save();
    }

    public function CheckBonusWin()
    {
        $allRateCnt = 0;
        $allRate = 0;
        foreach ($this->Paytable as $vl) {
            foreach ($vl as $vl2) {
                if ($vl2 > 0) {
                    $allRateCnt++;
                    $allRate += $vl2;
                    break;
                }
            }
        }
        return $allRate / $allRateCnt;
    }

    public function GetRandomPay()
    {
        $allRate = [];
        foreach ($this->Paytable as $vl) {
            foreach ($vl as $vl2) {
                if ($vl2 > 0) {
                    $allRate[] = $vl2;
                }
            }
        }
        shuffle($allRate);
        if ($this->game->stat_in < ($this->game->stat_out + ($allRate[0] * $this->AllBet))) {
            $allRate[0] = 0;
        }
        return $allRate[0];
    }

    public function HasGameDataStatic($key)
    {
        if (isset($this->gameDataStatic[$key])) {
            return true;
        } else {
            return false;
        }
    }

    public function SaveGameDataStatic()
    {
        $this->game->advanced = serialize($this->gameDataStatic);
        $this->game->save();
        $this->game->refresh();
    }

    public function SetGameDataStatic($key, $value)
    {
        $timeLife = 86400;
        $this->gameDataStatic[$key] = [
            'timelife' => time() + $timeLife,
            'payload' => $value
        ];
    }

    public function GetGameDataStatic($key)
    {
        if (isset($this->gameDataStatic[$key])) {
            return $this->gameDataStatic[$key]['payload'];
        } else {
            return 0;
        }
    }

    public function HasGameData($key)
    {
        if (isset($this->gameData[$key])) {
            return true;
        } else {
            return false;
        }
    }

    public function GetHistory()
    {

        $history = $this->betLogs;
        $this->lastEvent = 'NULL';
        foreach ($history as $log) {
            $tmpLog = json_decode($log->str);
            if ($tmpLog->responseEvent != 'gambleResult' && $tmpLog->responseEvent != 'jackpot') {
                $this->lastEvent = $log->str;
                break;
            }
        }
        if (isset($tmpLog)) {
            return $tmpLog;
        } else {
            return 'NULL';
        }
    }

    public function UpdateJackpots($bet)
    {
        // Safe return if no jackpots
        if (empty($this->jpgs)) return;

        $bet = $bet * $this->CurrentDenom;
        $count_balance = $this->count_balance;
        $jsum = [];
        $payJack = 0;
        for ($i = 0; $i < count($this->jpgs); $i++) {
            if ($count_balance == 0 || $this->jpgPercentZero) {
                $jsum[$i] = $this->jpgs[$i]->balance;
            } else if ($count_balance < $bet) {
                $jsum[$i] = $count_balance / 100 * $this->jpgs[$i]->percent + $this->jpgs[$i]->balance;
            } else {
                $jsum[$i] = $bet / 100 * $this->jpgs[$i]->percent + $this->jpgs[$i]->balance;
            }
            if ($this->jpgs[$i]->get_pay_sum() < $jsum[$i] && $this->jpgs[$i]->get_pay_sum() > 0) {
                $user_id = is_array($this->user) ? ($this->user['id'] ?? 0) : $this->user->id;
                if ($this->jpgs[$i]->user_id && $this->jpgs[$i]->user_id != $user_id) {
                } else {
                    $payJack = $this->jpgs[$i]->get_pay_sum() / $this->CurrentDenom;
                    $jsum[$i] = $jsum[$i] - $this->jpgs[$i]->get_pay_sum();
                    $this->SetBalance($this->jpgs[$i]->get_pay_sum() / $this->CurrentDenom);
                    if ($this->jpgs[$i]->get_pay_sum() > 0) {
                        $user_id = is_array($this->user) ? ($this->user['id'] ?? 0) : $this->user->id;
                        if ($this->jpgs[$i]->user_id && $this->jpgs[$i]->user_id != $user_id) {
                        } else {
                            $payJack = $this->jpgs[$i]->get_pay_sum() / $this->CurrentDenom;
                            $jsum[$i] = $jsum[$i] - $this->jpgs[$i]->get_pay_sum();
                            $this->SetBalance($this->jpgs[$i]->get_pay_sum() / $this->CurrentDenom);
                            $this->Jackpots['jackPay'] = $payJack;
                        }
                    }
                }
            }
            $this->jpgs[$i]->balance = $jsum[$i];
            if ($this->jpgs[$i]->balance < $this->jpgs[$i]->get_min('start_balance')) {
                $summ = $this->jpgs[$i]->get_start_balance();
                if ($summ > 0) {
                    $this->jpgs[$i]->add_jpg('add', $summ);
                }
            }
        }
        if ($payJack > 0) {
            $payJack = sprintf('%01.2f', $payJack);
            $this->Jackpots['jackPay'] = $payJack;
        }
    }

    public function GetBank($slotState = '')
    {
        if ($this->isBonusStart || $slotState == 'bonus' || $slotState == 'freespin' || $slotState == 'respin') {
            $slotState = 'bonus';
        } else {
            $slotState = '';
        }
        $game = $this->game;
        $this->Bank = $game->get_gamebank($slotState);
        return $this->Bank / $this->CurrentDenom;
    }

    public function GetPercent()
    {
        return $this->Percent;
    }

    public function GetCountBalanceUser()
    {
        return $this->user->count_balance;
    }

    public function InternalError($errcode)
    {
        throw new \Exception($errcode);
    }

    public function InternalErrorSilent($errcode)
    {
        Log::info('Internal Error Silent: ' . json_encode($errcode));
    }


    public function SetBank($slotState = '', $sum, $slotEvent = '')
    {
        if ($this->isBonusStart || $slotState == 'bonus' || $slotState == 'freespin' || $slotState == 'respin') {
            $slotState = 'bonus';
        } else {
            $slotState = '';
        }
        if ($this->GetBank($slotState) + $sum < 0) {
            $this->InternalError('Bank_   ' . $sum . '  CurrentBank_ ' . $this->GetBank($slotState) . ' CurrentState_ ' . $slotState . ' Trigger_ ' . ($this->GetBank($slotState) + $sum));
        }
        $sum = $sum * $this->CurrentDenom;
        $game = $this->game;
        $bankBonusSum = 0;
        if ($sum > 0 && $slotEvent == 'bet') {
            $this->toGameBanks = 0;
            $this->toSlotJackBanks = 0;
            $this->toSysJackBanks = 0;
            $this->betProfit = 0;
            $prc = $this->GetPercent();
            $prc_b = 10;
            if ($prc <= $prc_b) {
                $prc_b = 0;
            }
            $count_balance = $this->count_balance;
            $gameBet = $sum / $this->GetPercent() * 100;
            if ($count_balance < $gameBet && $count_balance > 0) {
                $firstBid = $count_balance;
                $secondBid = $gameBet - $firstBid;
                if (isset($this->betRemains0)) {
                    $secondBid = $this->betRemains0;
                }
                $bankSum = $firstBid / 100 * $this->GetPercent();
                $sum = $bankSum + $secondBid;
                $bankBonusSum = $firstBid / 100 * $prc_b;
            } else if ($count_balance > 0) {
                $bankBonusSum = $gameBet / 100 * $prc_b;
            }
            for ($i = 0; $i < count($this->jpgs); $i++) {
                if (!$this->jpgPercentZero) {
                    if ($count_balance < $gameBet && $count_balance > 0) {
                        $this->toSlotJackBanks += ($count_balance / 100 * $this->jpgs[$i]->percent);
                    } else if ($count_balance > 0) {
                        $this->toSlotJackBanks += ($gameBet / 100 * $this->jpgs[$i]->percent);
                    }
                }
            }
            $this->toGameBanks = $sum;

            $this->betProfit = $gameBet - $this->toGameBanks - $this->toSlotJackBanks - $this->toSysJackBanks;
        }
        if ($sum > 0) {
            $this->toGameBanks = $sum;
        }
        if ($bankBonusSum > 0) {
            $sum -= $bankBonusSum;
            $game->set_gamebank($bankBonusSum, 'inc', 'bonus');
        }
        if ($sum == 0 && $slotEvent == 'bet' && isset($this->betRemains)) {
            $sum = $this->betRemains;
        }
        $game->set_gamebank($sum, 'inc', $slotState);
        $game->save();
        return $game;
    }

    public function SetBalance($sum, $slotEvent = '')
    {
        if ($this->GetBalance() + $sum < 0) {
            $this->InternalError('Balance_   ' . $sum);
        }
        $sum = $sum * $this->CurrentDenom;
        if ($sum < 0 && $slotEvent == 'bet') {
            $user = $this->user;
            if ($user->count_balance == 0) {
                $remains = [];
                $this->betRemains = 0;
                $sm = abs($sum);
                if ($user->address < $sm && $user->address > 0) {
                    $remains[] = $sm - $user->address;
                }
                for ($i = 0; $i < count($remains); $i++) {
                    if ($this->betRemains < $remains[$i]) {
                        $this->betRemains = $remains[$i];
                    }
                }
            }
            if ($user->count_balance > 0 && $user->count_balance < abs($sum)) {
                $remains0 = [];
                $sm = abs($sum);
                $tmpSum = $sm - $user->count_balance;
                $this->betRemains0 = $tmpSum;
                if ($user->address > 0) {
                    $this->betRemains0 = 0;
                    if ($user->address < $tmpSum && $user->address > 0) {
                        $remains0[] = $tmpSum - $user->address;
                    }
                    for ($i = 0; $i < count($remains0); $i++) {
                        if ($this->betRemains0 < $remains0[$i]) {
                            $this->betRemains0 = $remains0;
                        }
                    }
                }
            }
            $sum0 = abs($sum);
            if ($user->count_balance == 0) {
                $sm = abs($sum);
                if ($user->address < $sm && $user->address > 0) {
                    $user->address = 0;
                } else if ($user->address > 0) {
                    $user->address -= $sm;
                }
            } else if ($user->count_balance > 0 && $user->count_balance < $sum0) {
                $sm = $sum0 - $user->count_balance;
                if ($user->address < $sm && $user->address > 0) {
                    $user->address = 0;
                } else if ($user->address > 0) {
                    $user->address -= $sm;
                }
            }
            $this->user->count_balance = $this->user->updateCountBalance($sum, $this->count_balance);
            $this->user->count_balance = $this->FormatFloat($this->user->count_balance);
        }
        $this->user->increment('balance', $sum);
        $this->user->balance = $this->FormatFloat($this->user->balance);
        $this->user->save();
        return $this->user;
    }

    public function GetBalance()
    {
        $user = $this->user;
        $this->Balance = $user->balance / $this->CurrentDenom;
        return $this->Balance;
    }

    public function SaveLogReport($response, $allbet, $lines, $reportWin, $slotEvent)
    {
        $this->logReport[] = [
            'response' => $response,
            'allbet' => $allbet,
            'lines' => $lines,
            'reportWin' => $reportWin,
            'slotEvent' => $slotEvent
        ];
    }


    public function GetGambleSettings()
    {
        $spinWin = rand(1, $this->WinGamble);
        return $spinWin;
    }

    public function getState()
    {
        return [
            'slotId' => $this->slotId,
            'playerId' => $this->playerId,
            'balance' => $this->GetBalance(),
            'gameData' => $this->GameData,
            'jackpots' => $this->Jackpots,
            'logReport' => $this->logReport,
            'internalError' => $this->internalError,
            'user_balance' => $this->GetBalance(),
            'game_bank' => $this->Bank
        ];
    }
}
