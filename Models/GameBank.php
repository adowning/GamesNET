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
