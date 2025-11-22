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
