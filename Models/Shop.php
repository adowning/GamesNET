<?php
namespace Models;

class Shop implements \ArrayAccess
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
    
    // ArrayAccess interface implementation for backward compatibility
    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset);
    }
    
    public function offsetGet($offset): mixed
    {
        return $this->$offset ?? null;
    }
    
    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }
    
    public function offsetUnset($offset): void
    {
        unset($this->$offset);
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
