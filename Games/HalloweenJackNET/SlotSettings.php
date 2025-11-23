<?php

namespace Games\HalloweenJackNET {
    class SlotSettings extends \Games\BaseSlotSettings
    {
        public $playerId = null;
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
        public $slotId = '';
        public $slotDBId = '';
        public $Line = null;
        public $scaleMode = null;
        public $numFloat = null;
        public $gameLine = null;
        public $Bet = null;
        public $isBonusStart = null;
        public $Balance = null;
        public $SymbolGame = null;
        public $GambleType = null;
        public $lastEvent = null;
        public $Jackpots = [];
        public $keyController = null;
        public $slotViewState = null;
        public $hideButtons = null;
        public $slotReelsConfig = null;
        public $slotFreeCount = null;
        public $slotFreeMpl = null;
        public $slotWildMpl = null;
        public $slotExitUrl = null;
        public $slotBonus = null;
        public $slotBonusType = null;
        public $slotScatterType = null;
        public $slotGamble = null;
        public $Paytable = [];
        public $slotSounds = [];
        public $jpgs = null;
        public $currency = null;
        public $user = null;
        public $game = null;
        public $shop = null;
        public $jpgPercentZero = false;
        public $count_balance = null;
        public $slotFastStop;
        public function __construct($settings)
        {
            parent::__construct($settings);


            $game = $this->game;
            $user = $this->user;
            $shop = $this->shop;
            $this->MaxWin = $this->shop->max_win;
            $this->increaseRTP = 1;
            $this->CurrentDenom = $this->game->denomination;
            $this->scaleMode = 0;
            $this->numFloat = 0;
            $this->Paytable['SYM_0'] = [
                0,
                0,
                0,
                0,
                0,
                0
            ];
            $this->Paytable['SYM_1'] = [
                0,
                0,
                0,
                0,
                0,
                0
            ];
            $this->Paytable['SYM_2'] = [
                0,
                0,
                0,
                0,
                0,
                0
            ];
            $this->Paytable['SYM_3'] = [
                0,
                0,
                0,
                20,
                200,
                1000
            ];
            $this->Paytable['SYM_4'] = [
                0,
                0,
                0,
                15,
                100,
                750
            ];
            $this->Paytable['SYM_5'] = [
                0,
                0,
                0,
                10,
                50,
                250
            ];
            $this->Paytable['SYM_6'] = [
                0,
                0,
                0,
                10,
                50,
                250
            ];
            $this->Paytable['SYM_7'] = [
                0,
                0,
                0,
                8,
                30,
                125
            ];
            $this->Paytable['SYM_8'] = [
                0,
                0,
                0,
                6,
                25,
                100
            ];
            $this->Paytable['SYM_9'] = [
                0,
                0,
                0,
                5,
                20,
                75
            ];
            $this->Paytable['SYM_10'] = [
                0,
                0,
                0,
                5,
                20,
                75
            ];
            $this->Paytable['SYM_11'] = [
                0,
                0,
                0,
                4,
                15,
                50
            ];
            $this->Paytable['SYM_12'] = [
                0,
                0,
                0,
                3,
                10,
                30
            ];
            $reel = new GameReel();
            foreach (
                [
                    'reelStrip1',
                    'reelStrip2',
                    'reelStrip3',
                    'reelStrip4',
                    'reelStrip5',
                    'reelStrip6'
                ] as $reelStrip
            ) {
                if (count($reel->reelsStrip[$reelStrip])) {
                    $this->$reelStrip = $reel->reelsStrip[$reelStrip];
                }
            }
            $this->keyController = [
                '13' => 'uiButtonSpin,uiButtonSkip',
                '49' => 'uiButtonInfo',
                '50' => 'uiButtonCollect',
                '51' => 'uiButtonExit2',
                '52' => 'uiButtonLinesMinus',
                '53' => 'uiButtonLinesPlus',
                '54' => 'uiButtonBetMinus',
                '55' => 'uiButtonBetPlus',
                '56' => 'uiButtonGamble',
                '57' => 'uiButtonRed',
                '48' => 'uiButtonBlack',
                '189' => 'uiButtonAuto',
                '187' => 'uiButtonSpin'
            ];
            $this->slotReelsConfig = [
                [
                    425,
                    142,
                    3
                ],
                [
                    669,
                    142,
                    3
                ],
                [
                    913,
                    142,
                    3
                ],
                [
                    1157,
                    142,
                    3
                ],
                [
                    1401,
                    142,
                    3
                ]
            ];
            $this->slotBonusType = 1;
            $this->slotScatterType = 0;
            $this->splitScreen = false;
            $this->slotBonus = true;
            $this->slotGamble = true;
            $this->slotFastStop = 1;
            $this->slotExitUrl = '/';
            $this->slotWildMpl = 3;
            $this->GambleType = 1;

            $this->CurrentDenom = $this->Denominations[0];
            $this->CurrentDenomination = $this->Denominations[0];
            $this->slotFreeCount = [
                0,
                0,
                0,
                10,
                10,
                10
            ];
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($game->slotViewState == '' ? 'Normal' : $game->slotViewState);
            $this->hideButtons = [];

            $this->slotJackPercent = [];
            $this->slotJackpot = [];
            for ($jp = 1; $jp <= 4; $jp++) {
                $this->slotJackpot[] = $game->{'jp_' . $jp};
                $this->slotJackPercent[] = $game->{'jp_' . $jp . '_percent'};
            }
            $this->Line = [
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15
            ];
            $this->gameLine = [
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15
            ];
            $this->Bet = explode(',', $game->bet);
            $this->Balance = $user->balance;
            $this->SymbolGame = [
                '1',
                '3',
                '4',
                '5',
                '6',
                '7',
                '8',
                '9',
                '10',
                '11',
                '12'
            ];
            $this->Bank = $game->get_gamebank();
            $this->Percent = $this->shop->percent;
            $this->WinGamble = $game->rezerv;
            $this->slotDBId = $game->id;
            $this->slotCurrency = $user->shop->currency;
            $this->count_balance = $user->count_balance;
            if ($user->address > 0 && $user->count_balance == 0) {
                $this->Percent = 0;
                $this->jpgPercentZero = true;
            } else if ($user->count_balance == 0) {
                $this->Percent = 100;
            }
            if (!isset($this->user->session) || strlen($this->user->session) <= 0) {
                $this->user->session = serialize([]);
            }

            // Safe unserialization for gameData
            $this->gameData = @unserialize($this->user->session);
            if (!is_array($this->gameData)) {
                $this->gameData = [];
            }

            // Clean up expired gameData
            if (count($this->gameData) > 0) {
                foreach ($this->gameData as $key => $vl) {
                    if (isset($vl['timelife']) && $vl['timelife'] <= time()) {
                        unset($this->gameData[$key]);
                    }
                }
            }

            if (!isset($this->game->advanced) || strlen($this->game->advanced) <= 0) {
                $this->game->advanced = serialize([]);
            }

            // Safe unserialization for gameDataStatic
            $this->gameDataStatic = @unserialize($this->game->advanced);
            if (!is_array($this->gameDataStatic)) {
                $this->gameDataStatic = [];
            }

            // Clean up expired gameDataStatic
            if (count($this->gameDataStatic) > 0) {
                foreach ($this->gameDataStatic as $key => $vl) {
                    if (isset($vl['timelife']) && $vl['timelife'] <= time()) {
                        unset($this->gameDataStatic[$key]);
                    }
                }
            }
        }


        public function GetSpinSettings($bet, $lines, $garantType = 'bet')
        {
            $curField = 10;
            switch ($lines) {
                case 10:
                    $curField = 10;
                    break;
                case 9:
                case 8:
                    $curField = 9;
                    break;
                case 7:
                case 6:
                    $curField = 7;
                    break;
                case 5:
                case 4:
                    $curField = 5;
                    break;
                case 3:
                case 2:
                    $curField = 3;
                    break;
                case 1:
                    $curField = 1;
                    break;
                default:
                    $curField = 10;
                    break;
            }
            if ($garantType != 'bet') {
                $pref = '_bonus';
            } else {
                $pref = '';
            }
            $this->AllBet = $bet * $lines;
            $linesPercentConfigSpin = $this->game->get_lines_percent_config('spin');
            $linesPercentConfigBonus = $this->game->get_lines_percent_config('bonus');
            $currentPercent = $this->shop->percent;
            $currentSpinWinChance = 0;
            $currentBonusWinChance = 0;
            $percentLevel = '';

            // Validate that the configuration array exists and has data
            $spinConfigKey = 'line' . $curField . $pref;
            $bonusConfigKey = 'line' . $curField . $pref;

            if (isset($linesPercentConfigSpin[$spinConfigKey]) && is_array($linesPercentConfigSpin[$spinConfigKey])) {
                foreach ($linesPercentConfigSpin[$spinConfigKey] as $k => $v) {
                    $l = explode('_', $k);
                    if (count($l) >= 2) {
                        $l0 = $l[0];
                        $l1 = $l[1];
                        if (is_numeric($l0) && is_numeric($l1) && is_numeric($currentPercent)) {
                            if ($l0 <= $currentPercent && $currentPercent <= $l1) {
                                $percentLevel = $k;
                                break;
                            }
                        }
                    }
                }
            }

            // If no matching percent level found, use the first available key or fallback to default
            if ($percentLevel === '') {
                if (isset($linesPercentConfigSpin[$spinConfigKey]) && !empty($linesPercentConfigSpin[$spinConfigKey])) {
                    // Use the first available configuration key
                    $percentLevel = array_keys($linesPercentConfigSpin[$spinConfigKey])[0];
                } else {
                    // Fallback to safe defaults based on game type
                    $percentLevel = ($pref === '_bonus') ? '1_100' : '1_100';
                }
            }


            // Safe array access with validation
            $currentSpinWinChance = isset($linesPercentConfigSpin[$spinConfigKey][$percentLevel])
                ? $linesPercentConfigSpin[$spinConfigKey][$percentLevel] : 100;
            $currentBonusWinChance = isset($linesPercentConfigBonus[$bonusConfigKey][$percentLevel])
                ? $linesPercentConfigBonus[$bonusConfigKey][$percentLevel] : 1000;
            $RtpControlCount = 200;
            if (!$this->HasGameDataStatic('SpinWinLimit')) {
                $this->SetGameDataStatic('SpinWinLimit', 0);
            }
            if (!$this->HasGameDataStatic('RtpControlCount')) {
                $this->SetGameDataStatic('RtpControlCount', $RtpControlCount);
            }
            if ($this->game->stat_in > 0) {
                $rtpRange = $this->game->stat_out / $this->game->stat_in * 100;
            } else {
                $rtpRange = 0;
            }
            if ($this->GetGameDataStatic('RtpControlCount') == 0) {
                if ($currentPercent + rand(1, 2) < $rtpRange && $this->GetGameDataStatic('SpinWinLimit') <= 0) {
                    $this->SetGameDataStatic('SpinWinLimit', rand(25, 50));
                }
                if ($pref == '' && $this->GetGameDataStatic('SpinWinLimit') > 0) {
                    $currentBonusWinChance = 5000;
                    $currentSpinWinChance = 20;
                    $this->MaxWin = rand(1, 5);
                    if ($rtpRange < ($currentPercent - 1)) {
                        $this->SetGameDataStatic('SpinWinLimit', 0);
                        $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
                    }
                }
            } else if ($this->GetGameDataStatic('RtpControlCount') < 0) {
                if ($currentPercent + rand(1, 2) < $rtpRange && $this->GetGameDataStatic('SpinWinLimit') <= 0) {
                    $this->SetGameDataStatic('SpinWinLimit', rand(25, 50));
                }
                $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
                if ($pref == '' && $this->GetGameDataStatic('SpinWinLimit') > 0) {
                    $currentBonusWinChance = 5000;
                    $currentSpinWinChance = 20;
                    $this->MaxWin = rand(1, 5);
                    if ($rtpRange < ($currentPercent - 1)) {
                        $this->SetGameDataStatic('SpinWinLimit', 0);
                    }
                }
                if ($this->GetGameDataStatic('RtpControlCount') < (-1 * $RtpControlCount) && $currentPercent - 1 <= $rtpRange && $rtpRange <= ($currentPercent + 2)) {
                    $this->SetGameDataStatic('RtpControlCount', $RtpControlCount);
                }
            } else {
                $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
            }
            $bonusWin = rand(1, $currentBonusWinChance);
            $spinWin = rand(1, $currentSpinWinChance);
            $return = [
                'none',
                0
            ];
            if ($bonusWin == 1 && $this->slotBonus) {
                $this->isBonusStart = true;
                $garantType = 'bonus';
                $winLimit = $this->GetBank($garantType);
                $return = [
                    'bonus',
                    $winLimit
                ];
                if ($this->game->stat_in < ($this->CheckBonusWin() * $bet + $this->game->stat_out) || $winLimit < ($this->CheckBonusWin() * $bet)) {
                    $return = [
                        'none',
                        0
                    ];
                }
            } else if ($spinWin == 1) {
                $winLimit = $this->GetBank($garantType);
                $return = [
                    'win',
                    $winLimit
                ];
            }
            if ($garantType == 'bet' && $this->GetBalance() <= (2 / $this->CurrentDenom)) {
                $randomPush = rand(1, 10);
                if ($randomPush == 1) {
                    $winLimit = $this->GetBank('');
                    $return = [
                        'win',
                        $winLimit
                    ];
                }
            }
            return $return;
        }
        public function getNewSpin($game, $lines, $spinWin = 0, $bonusWin = 0, $garantType = 'bet')
        {
            $curField = 10;
            switch ($lines) {
                case 10:
                    $curField = 10;
                    break;
                case 9:
                case 8:
                    $curField = 9;
                    break;
                case 7:
                case 6:
                    $curField = 7;
                    break;
                case 5:
                case 4:
                    $curField = 5;
                    break;
                case 3:
                case 2:
                    $curField = 3;
                    break;
                case 1:
                    $curField = 1;
                    break;
                default:
                    $curField = 10;
                    break;
            }
            if ($garantType != 'bet') {
                $pref = '_bonus';
            } else {
                $pref = '';
            }
            if ($spinWin) {
                $win = explode(',', $game->game_win->{'winline' . $pref . $curField});
            }
            if ($bonusWin) {
                $win = explode(',', $game->game_win->{'winbonus' . $pref . $curField});
            }
            $number = rand(0, count($win) - 1);
            return $win[$number];
        }
        public function GetRandomScatterPos($rp)
        {
            $rpResult = [];
            for ($i = 0; $i < count($rp); $i++) {
                if ($rp[$i] == '0') {
                    if (isset($rp[$i + 1]) && isset($rp[$i - 1])) {
                        array_push($rpResult, $i);
                    }
                    if (isset($rp[$i - 1]) && isset($rp[$i - 2])) {
                        array_push($rpResult, $i - 1);
                    }
                    if (isset($rp[$i + 1]) && isset($rp[$i + 2])) {
                        array_push($rpResult, $i + 1);
                    }
                }
            }
            shuffle($rpResult);
            if (!isset($rpResult[0])) {
                $rpResult[0] = rand(2, count($rp) - 3);
            }
            return $rpResult[0];
        }
        public function GetGambleSettings()
        {
            $spinWin = rand(1, $this->WinGamble);
            return $spinWin;
        }
        public function GetReelStrips($winType, $slotEvent)
        {
            $game = $this->game;
            if ($slotEvent == 'freespin') {
                $reel = new GameReel();
                $fArr = $reel->reelsStripBonus;
                foreach (
                    [
                        'reelStrip1',
                        'reelStrip2',
                        'reelStrip3',
                        'reelStrip4',
                        'reelStrip5',
                        'reelStrip6'
                    ] as $reelStrip
                ) {
                    $curReel = array_shift($fArr);
                    if (count($curReel)) {
                        $this->$reelStrip = $curReel;
                    }
                }
            }
            if ($winType != 'bonus') {
                $prs = [];
                foreach (
                    [
                        'reelStrip1',
                        'reelStrip2',
                        'reelStrip3',
                        'reelStrip4',
                        'reelStrip5',
                        'reelStrip6'
                    ] as $index => $reelStrip
                ) {
                    if (is_array($this->$reelStrip) && count($this->$reelStrip) > 0) {
                        $prs[$index + 1] = mt_rand(0, count($this->$reelStrip) - 3);
                    }
                }
            } else {
                $reelsId = [];
                foreach (
                    [
                        'reelStrip1',
                        'reelStrip2',
                        'reelStrip3',
                        'reelStrip4',
                        'reelStrip5',
                        'reelStrip6'
                    ] as $index => $reelStrip
                ) {
                    if (is_array($this->$reelStrip) && count($this->$reelStrip) > 0) {
                        $prs[$index + 1] = $this->GetRandomScatterPos($this->$reelStrip);
                        $reelsId[] = $index + 1;
                    }
                }
                $scattersCnt = rand(3, count($reelsId));
                shuffle($reelsId);
                for ($i = 0; $i < count($reelsId); $i++) {
                    if ($i < $scattersCnt) {
                        $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->{'reelStrip' . $reelsId[$i]});
                    } else {
                        $prs[$reelsId[$i]] = rand(0, count($this->{'reelStrip' . $reelsId[$i]}) - 3);
                    }
                }
            }
            $reel = [
                'rp' => []
            ];
            foreach ($prs as $index => $value) {
                $key = $this->{'reelStrip' . $index};
                $cnt = count($key);
                $key[-1] = $key[$cnt - 1];
                $key[$cnt] = $key[0];
                $reel['reel' . $index][0] = $key[$value - 1];
                $reel['reel' . $index][1] = $key[$value];
                $reel['reel' . $index][2] = $key[$value + 1];
                $reel['reel' . $index][3] = '';
                $reel['rp'][] = $value;
            }
            return $reel;
        }
    }
}
