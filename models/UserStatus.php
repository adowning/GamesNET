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
