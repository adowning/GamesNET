<?php

namespace Games\GoBananasNET {
    class SlotSettings extends \Games\BaseSlotSettings
    {
        public $slotFastStop;
        public function __construct($settings)
        {
            parent::__construct($settings);

            // Paytable initialization from original file
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
                25,
                120,
                700
            ];
            $this->Paytable['SYM_4'] = [
                0,
                0,
                0,
                20,
                80,
                350
            ];
            $this->Paytable['SYM_5'] = [
                0,
                0,
                0,
                15,
                60,
                250
            ];
            $this->Paytable['SYM_6'] = [
                0,
                0,
                0,
                15,
                50,
                180
            ];
            $this->Paytable['SYM_7'] = [
                0,
                0,
                0,
                10,
                40,
                140
            ];
            $this->Paytable['SYM_8'] = [
                0,
                0,
                0,
                5,
                20,
                70
            ];
            $this->Paytable['SYM_9'] = [
                0,
                0,
                0,
                5,
                15,
                60
            ];
            $this->Paytable['SYM_10'] = [
                0,
                0,
                0,
                5,
                15,
                50
            ];
            $this->Paytable['SYM_11'] = [
                0,
                0,
                0,
                5,
                10,
                40
            ];
            $this->Paytable['SYM_12'] = [
                0,
                0,
                0,
                5,
                10,
                30
            ];

            // Game-specific initialization
            $this->scaleMode = 0;
            $this->numFloat = 0;

            $this->slotBonusType = 1;
            $this->slotScatterType = 0;
            $this->splitScreen = false;
            $this->slotBonus = false;
            $this->slotGamble = true;
            $this->slotFastStop = 1;
            $this->slotExitUrl = '/';
            $this->slotWildMpl = 1;
            $this->GambleType = 1;

            $this->slotFreeCount = 1;
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($this->game->slotViewState == '' ? 'Normal' : $this->game->slotViewState);
            $this->hideButtons = [];

            $this->Bet = explode(',', $this->game->bet);
            $this->Balance = $this->user->balance;
            $this->SymbolGame = [
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
                12
            ];
        }

        // Game-specific methods


        public function GetSpinSettings($garantType = 'bet', $bet, $lines)
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

        public function getNewSpin($game, $spinWin = 0, $bonusWin = 0, $lines, $garantType = 'bet')
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

        public function GetReelStrips($winType, $slotEvent)
        {
            $game = $this->game;
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
        // Game-specific methods restored from backup

    }
}
