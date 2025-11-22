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
