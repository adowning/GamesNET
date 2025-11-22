<?php
namespace Models;

class Game
{
    private array $originalData;
    private array $changedData = [];
    private bool $isModified = false;
    
    public int $id;
    public string $name;
    public int $shop_id;
    public float $stat_in = 0;
    public float $stat_out = 0;
    public int $bids = 0;
    public float $denomination = 1.0;
    public string $slotViewState = '';
    public string $bet = '0.1,0.2,0.5,1,2,5';
    public array $jp_config = [];
    public float $rezerv = 100;
    public bool $view = true;
    public string $advanced = '';
    
    public function __construct(array $data = [])
    {
        $this->originalData = $data;
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->shop_id = $data['shop_id'] ?? 0;
        $this->stat_in = $data['stat_in'] ?? 0;
        $this->stat_out = $data['stat_out'] ?? 0;
        $this->bids = $data['bids'] ?? 0;
        $this->denomination = $data['denomination'] ?? 1.0;
        $this->slotViewState = $data['slotViewState'] ?? '';
        $this->bet = $data['bet'] ?? '0.1,0.2,0.5,1,2,5';
        $this->jp_config = $data['jp_config'] ?? [];
        $this->rezerv = $data['rezerv'] ?? 100;
        $this->view = $data['view'] ?? true;
        $this->advanced = $data['advanced'] ?? '';
    }
    
    public function __set($name, $value)
    {
        // Handle dynamic jp_1, jp_1_percent properties
        if (strpos($name, 'jp_') === 0) {
            if (substr($name, -7) === '_percent') {
                $jpNum = substr($name, 3, -8);
                $this->jp_config['jp_' . $jpNum . '_percent'] = $value;
            } else {
                $jpNum = substr($name, 3);
                $this->jp_config['jp_' . $jpNum] = $value;
            }
        } elseif (property_exists($this, $name)) {
            $this->$name = $value;
            $this->changedData[$name] = $value;
            $this->isModified = true;
        }
    }
    
    public function __get($name)
    {
        // Handle dynamic jp_1, jp_1_percent properties
        if (strpos($name, 'jp_') === 0) {
            if (substr($name, -7) === '_percent') {
                $jpNum = substr($name, 3, -8);
                return $this->jp_config['jp_' . $jpNum . '_percent'] ?? 0;
            } else {
                $jpNum = substr($name, 3);
                return $this->jp_config['jp_' . $jpNum] ?? 0;
            }
        }
        
        return null;
    }
    
    public function getGameBank(string $slotState = ''): float
    {
        // Return game bank amount based on slot state
        // This would be calculated based on your bank's logic
        $bankKey = $slotState === 'bonus' ? 'bonus_bank' : 'main_bank';
        return $this->jp_config[$bankKey] ?? 1000.0;
    }
    
    public function getLinesPercentConfig(string $type): array
    {
        // Return RTP configuration for spin/bonus
        // This would be game-specific configuration
        return $this->jp_config['lines_percent_config'][$type] ?? [
            'line10' => ['0_100' => 20],
            'line9' => ['0_100' => 25],
            'line5' => ['0_100' => 30]
        ];
    }
    
    public function increment(string $field, float $amount = 1): void
    {
        if (property_exists($this, $field)) {
            $oldValue = $this->$field ?? 0;
            $this->$field += $amount;
            
            if ($oldValue !== $this->$field) {
                $this->changedData[$field] = $this->$field;
                $this->isModified = true;
            }
        }
    }
    
    public function refresh(): void
    {
        // No-op for stateless operation
    }
    
    public function tournamentStat(string $slotState, int $userId, float $bet, float $win): void
    {
        // Track tournament statistics
        if (!isset($this->tournament_stats)) {
            $this->tournament_stats = [];
        }
        
        $key = $slotState . '_' . $userId;
        if (!isset($this->tournament_stats[$key])) {
            $this->tournament_stats[$key] = [
                'bet' => 0,
                'win' => 0,
                'count' => 0
            ];
        }
        
        $this->tournament_stats[$key]['bet'] += $bet;
        $this->tournament_stats[$key]['win'] += $win;
        $this->tournament_stats[$key]['count']++;
    }
    
    public function setGameBank(float $amount, string $operation, string $slotState): void
    {
        // Update game bank amount
        $bankKey = $slotState === 'bonus' ? 'bonus_bank' : 'main_bank';
        if (!isset($this->jp_config[$bankKey])) {
            $this->jp_config[$bankKey] = 0;
        }
        
        if ($operation === 'inc') {
            $this->jp_config[$bankKey] += $amount;
        } else {
            $this->jp_config[$bankKey] -= $amount;
        }
    }
    
    public function save(): void
    {
        if ($this->isModified) {
            $this->changedData['id'] = $this->id;
        }
    }
    
    public function hasChanges(): bool
    {
        return $this->isModified;
    }
    
    public function getChanges(): array
    {
        return $this->changedData;
    }
    
    public function getState(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shop_id' => $this->shop_id,
            'stat_in' => $this->stat_in,
            'stat_out' => $this->stat_out,
            'bids' => $this->bids,
            'denomination' => $this->denomination,
            'slotViewState' => $this->slotViewState,
            'bet' => $this->bet,
            'jp_config' => $this->jp_config,
            'rezerv' => $this->rezerv,
            'view' => $this->view,
            'advanced' => $this->advanced
        ];
    }
}
