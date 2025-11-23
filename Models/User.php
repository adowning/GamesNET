<?php

namespace Models;

class User implements \ArrayAccess
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
    public object $shop;

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

        // Initialize shop object with default currency
        $shop = $data['shop'] ?? [];
        $this->shop = (object) [
            'currency' => $shop['currency'] ?? 'USD'
        ];
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

    public function __set($name, $value): void
    {
        if (property_exists($this, $name)) {
            $oldValue = $this->$name ?? null;
            $this->$name = $value;

            if ($oldValue !== $value) {
                // Special handling for shop object to track its currency changes
                if ($name === 'shop') {
                    $this->changedData[$name] = [
                        'currency' => $value->currency ?? 'USD'
                    ];
                } else {
                    $this->changedData[$name] = $value;
                }
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
            'last_bid' => $this->last_bid,
            'shop' => [
                'currency' => $this->shop->currency
            ]
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
