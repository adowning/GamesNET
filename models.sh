#!/bin/bash

# PSR-4 Compatible Models Generator Script
# Creates stateless model classes for the casino project

echo "Creating PSR-4 Compatible Models..."

# Clean up old autoloader
rm -f Models/autoload.php

# Create directory structure
mkdir -p Models/Services

echo "Creating User Model..."
cat > Models/User.php << 'EOF'
<?php
namespace Models;

class User
{
    private array $originalData;
    private array $changedData = [];
    private bool $isModified = false;
    
    public int $id;
    public float $balance;
    public int $shop_id;
    public float $count_balance;
    public float $address = 0;
    public string $session = '';
    public bool $is_blocked = false;
    public string $status = 'active';
    public ?string $remember_token = null;
    public ?string $last_bid = null;
    
    public function __construct(array $data = [])
    {
        $this->originalData = $data;
        $this->id = $data['id'] ?? 0;
        $this->balance = $data['balance'] ?? 0.0;
        $this->shop_id = $data['shop_id'] ?? 0;
        $this->count_balance = $data['count_balance'] ?? 0.0;
        $this->address = $data['address'] ?? 0.0;
        $this->session = $data['session'] ?? '';
        $this->is_blocked = $data['is_blocked'] ?? false;
        $this->status = $data['status'] ?? 'active';
        $this->remember_token = $data['remember_token'] ?? null;
        $this->last_bid = $data['last_bid'] ?? null;
    }
    
    public function __set($name, $value): void
    {
        if (property_exists($this, $name)) {
            $oldValue = $this->$name ?? null;
            $this->$name = $value;
            
            if ($oldValue !== $value) {
                $this->changedData[$name] = $value;
                $this->isModified = true;
            }
        }
    }
    
    public function increment(string $field, float $amount): void
    {
        if (property_exists($this, $field)) {
            $oldValue = $this->$field ?? 0;
            $this->$field += $amount;
            
            $this->changedData[$field] = $this->$field;
            $this->isModified = true;
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
            'balance' => $this->balance,
            'shop_id' => $this->shop_id,
            'count_balance' => $this->count_balance,
            'address' => $this->address,
            'session' => $this->session,
            'is_blocked' => $this->is_blocked,
            'status' => $this->status,
            'remember_token' => $this->remember_token,
            'last_bid' => $this->last_bid
        ];
    }
    
    public function reset(): void
    {
        foreach ($this->originalData as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->changedData = [];
        $this->isModified = false;
    }
    
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function updateLevel(string $type, float $amount): void
    {
        if (!isset($this->level_data)) {
            $this->level_data = [];
        }
        $this->level_data[$type] = ($this->level_data[$type] ?? 0) + $amount;
    }
    
    public function updateCountBalance(float $sum, float $current): float
    {
        $this->count_balance = $current + $sum;
        return $this->count_balance;
    }
    
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }
}
EOF

echo "Creating Game Model..."
cat > Models/Game.php << 'EOF'
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
        $bankKey = $slotState === 'bonus' ? 'bonus_bank' : 'main_bank';
        return $this->jp_config[$bankKey] ?? 1000.0;
    }
    
    public function getLinesPercentConfig(string $type): array
    {
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
EOF

echo "Creating Shop Model..."
cat > Models/Shop.php << 'EOF'
<?php
namespace Models;

class Shop
{
    public int $id;
    public float $max_win;
    public float $percent;
    public bool $is_blocked = false;
    public string $currency = 'USD';
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->max_win = $data['max_win'] ?? 1000.0;
        $this->percent = $data['percent'] ?? 10.0;
        $this->is_blocked = $data['is_blocked'] ?? false;
        $this->currency = $data['currency'] ?? 'USD';
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'max_win' => $this->max_win,
            'percent' => $this->percent,
            'is_blocked' => $this->is_blocked,
            'currency' => $this->currency
        ];
    }
}
EOF

echo "Creating JPG Model..."
cat > Models/JPG.php << 'EOF'
<?php
namespace Models;

class JPG
{
    public int $id;
    public int $shop_id;
    public float $balance = 0;
    public float $percent = 1.0;
    public ?int $user_id = null;
    public float $start_balance = 1000;
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->shop_id = $data['shop_id'] ?? 0;
        $this->balance = $data['balance'] ?? 0.0;
        $this->percent = $data['percent'] ?? 1.0;
        $this->user_id = $data['user_id'] ?? null;
        $this->start_balance = $data['start_balance'] ?? 1000.0;
    }
    
    public function getPaySum(): float
    {
        return $this->balance > 10000 ? 1000.0 : 0.0;
    }
    
    public function getMin(string $field): float
    {
        return $this->start_balance * 0.5;
    }
    
    public function getStartBalance(): float
    {
        return $this->start_balance;
    }
    
    public function addJpg(string $operation, float $amount): void
    {
        if ($operation === 'add') {
            $this->balance += $amount;
        }
    }
    
    public function save(): void
    {
        // No-op for stateless operation
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'balance' => $this->balance,
            'percent' => $this->percent,
            'user_id' => $this->user_id,
            'start_balance' => $this->start_balance
        ];
    }
}
EOF

echo "Creating GameBank Model..."
cat > Models/GameBank.php << 'EOF'
<?php
namespace Models;

class GameBank
{
    public int $id;
    public int $shop_id;
    public float $balance = 0;
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->shop_id = $data['shop_id'] ?? 0;
        $this->balance = $data['balance'] ?? 0.0;
    }
    
    public static function where(array $conditions): self
    {
        return new self($conditions);
    }
    
    public function lockForUpdate(): self
    {
        return $this;
    }
    
    public function get(): array
    {
        return [$this];
    }
    
    public function first(): ?self
    {
        return $this->id > 0 ? $this : null;
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'balance' => $this->balance
        ];
    }
}
EOF

echo "Creating GameLog Model..."
cat > Models/GameLog.php << 'EOF'
<?php
namespace Models;

class GameLog
{
    public int $id;
    public int $game_id;
    public int $user_id;
    public string $ip = '';
    public string $str = '';
    public int $shop_id;
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->game_id = $data['game_id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->ip = $data['ip'] ?? '';
        $this->str = $data['str'] ?? '';
        $this->shop_id = $data['shop_id'] ?? 0;
    }
    
    public static function whereRaw(string $query, array $bindings = []): self
    {
        return new self(['game_id' => $bindings[0] ?? 0, 'user_id' => $bindings[1] ?? 0]);
    }
    
    public function get(): array
    {
        return [$this];
    }
    
    public static function create(array $data): self
    {
        return new self($data);
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
            'ip' => $this->ip,
            'str' => $this->str,
            'shop_id' => $this->shop_id
        ];
    }
}
EOF

echo "Creating StatGame Model..."
cat > Models/StatGame.php << 'EOF'
<?php
namespace Models;

class StatGame
{
    public int $id;
    public int $user_id;
    public float $balance;
    public float $bet;
    public float $win;
    public string $game;
    public float $in_game;
    public float $in_jpg;
    public float $in_profit;
    public float $denomination;
    public int $shop_id;
    public float $slots_bank;
    public float $bonus_bank;
    public float $fish_bank;
    public float $table_bank;
    public float $little_bank;
    public float $total_bank;
    public string $date_time;
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->balance = $data['balance'] ?? 0.0;
        $this->bet = $data['bet'] ?? 0.0;
        $this->win = $data['win'] ?? 0.0;
        $this->game = $data['game'] ?? '';
        $this->in_game = $data['in_game'] ?? 0.0;
        $this->in_jpg = $data['in_jpg'] ?? 0.0;
        $this->in_profit = $data['in_profit'] ?? 0.0;
        $this->denomination = $data['denomination'] ?? 1.0;
        $this->shop_id = $data['shop_id'] ?? 0;
        $this->slots_bank = $data['slots_bank'] ?? 0.0;
        $this->bonus_bank = $data['bonus_bank'] ?? 0.0;
        $this->fish_bank = $data['fish_bank'] ?? 0.0;
        $this->table_bank = $data['table_bank'] ?? 0.0;
        $this->little_bank = $data['little_bank'] ?? 0.0;
        $this->total_bank = $data['total_bank'] ?? 0.0;
        $this->date_time = $data['date_time'] ?? date('Y-m-d H:i:s');
    }
    
    public static function create(array $data): self
    {
        return new self($data);
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'balance' => $this->balance,
            'bet' => $this->bet,
            'win' => $this->win,
            'game' => $this->game,
            'in_game' => $this->in_game,
            'in_jpg' => $this->in_jpg,
            'in_profit' => $this->in_profit,
            'denomination' => $this->denomination,
            'shop_id' => $this->shop_id,
            'slots_bank' => $this->slots_bank,
            'bonus_bank' => $this->bonus_bank,
            'fish_bank' => $this->fish_bank,
            'table_bank' => $this->table_bank,
            'little_bank' => $this->little_bank,
            'total_bank' => $this->total_bank,
            'date_time' => $this->date_time
        ];
    }
}
EOF

echo "Creating Session Model..."
cat > Models/Session.php << 'EOF'
<?php
namespace Models;

class Session
{
    public int $id;
    public int $user_id;
    public string $payload = '';
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->payload = $data['payload'] ?? '';
    }
    
    public static function where(string $field, $value): self
    {
        return new self([$field => $value]);
    }
    
    public static function whereUserId(int $userId): self
    {
        return new self(['user_id' => $userId]);
    }
    
    public function delete(): void
    {
        // No-op for stateless operation
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payload' => $this->payload
        ];
    }
}
EOF

echo "Creating Banker Static Class..."
cat > Models/Banker.php << 'EOF'
<?php
namespace Models;

class Banker
{
    public static function getAllBanks(int $shopId): array
    {
        return [
            'slots' => 10000.0,
            'bonus' => 5000.0,
            'fish' => 2000.0,
            'table' => 3000.0,
            'little' => 1000.0
        ];
    }
    
    public static function getSlotBanks(int $shopId): array
    {
        $banks = self::getAllBanks($shopId);
        return [
            $banks['slots'],
            $banks['bonus'],
            $banks['fish'],
            $banks['table'],
            $banks['little']
        ];
    }
}
EOF

echo "Creating UserStatus Static Class..."
cat > Models/UserStatus.php << 'EOF'
<?php
namespace Models;

class UserStatus
{
    const BANNED = 'banned';
    const ACTIVE = 'active';
    const SUSPENDED = 'suspended';
    
    public static function isBanned(string $status): bool
    {
        return $status === self::BANNED;
    }
}
EOF

echo "Creating StateManager Service..."
cat > Models/Services/StateManager.php << 'EOF'
<?php
namespace Services;

class StateManager
{
    private array $models = [];
    
    public function registerModel(string $type, object $model): void
    {
        $this->models[$type] = $model;
    }
    
    public function getChangedModels(): array
    {
        $changedModels = [];
        
        foreach ($this->models as $type => $model) {
            if (method_exists($model, 'hasChanges') && $model->hasChanges()) {
                $changedModels[$type] = $model->getChanges();
            }
        }
        
        return $changedModels;
    }
    
    public function getAllStates(): array
    {
        $states = [];
        
        foreach ($this->models as $type => $model) {
            if (method_exists($model, 'getState')) {
                $states[$type] = $model->getState();
            }
        }
        
        return $states;
    }
    
    public function markAllForSave(): void
    {
        foreach ($this->models as $model) {
            if (method_exists($model, 'save')) {
                $model->save();
            }
        }
    }
}
EOF

echo "Models created successfully!"
echo ""
echo "Created PSR-4 compatible models:"
echo "├── Models/"
echo "│   ├── User.php"
echo "│   ├── Game.php"
echo "│   ├── Shop.php"
echo "│   ├── JPG.php"
echo "│   ├── GameBank.php"
echo "│   ├── GameLog.php"
echo "│   ├── StatGame.php"
echo "│   ├── Session.php"
echo "│   ├── Banker.php"
echo "│   ├── UserStatus.php"
echo "│   └── Services/"
echo "│       └── StateManager.php"
echo ""
echo "Next steps:"
echo "1. Run: composer dump-autoload"
echo "2. Run: php workerman_server_example.php start"
echo "3. Test the server is working with: curl http://localhost:8080"
EOF
