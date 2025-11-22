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
        // Factory method for where raw queries
        return new self(['game_id' => $bindings[0] ?? 0, 'user_id' => $bindings[1] ?? 0]);
    }
    
    public function get(): array
    {
        // Return collection of GameLog instances
        return [$this]; // Simplified
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
