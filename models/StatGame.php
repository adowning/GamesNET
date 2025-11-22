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
