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
        // Return current jackpot payout amount
        // This would be calculated based on your jackpot rules
        return $this->balance > 10000 ? 1000.0 : 0.0;
    }
    
    public function getMin(string $field): float
    {
        // Return minimum balance for jackpot
        return $this->start_balance * 0.5; // 50% of start balance
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
