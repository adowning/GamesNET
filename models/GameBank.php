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
        // Factory method to create instance based on conditions
        return new self($conditions);
    }
    
    public function lockForUpdate(): self
    {
        // No-op for stateless operation
        return $this;
    }
    
    public function get(): array
    {
        // Return collection of GameBank instances
        // In real implementation, this would query the database
        return [$this]; // Simplified for stateless operation
    }
    
    public function first(): ?self
    {
        // Return first result or null
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
