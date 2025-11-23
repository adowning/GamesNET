<?php
namespace Models;

/**
 * ModelFactory - Converts client array data to proper Model objects
 * 
 * This factory handles conversion of raw arrays from TypeScript client
 * to PHP Model objects while maintaining backward compatibility.
 */
class ModelFactory
{
    /**
     * Create a User model from array data
     * 
     * @param array $data Raw user data from client
     * @return User
     */
    public static function createUser(array $data): User
    {
        return new User($data);
    }

    /**
     * Create a Game model from array data
     * 
     * @param array $data Raw game data from client
     * @return Game
     */
    public static function createGame(array $data): Game
    {
        return new Game($data);
    }

    /**
     * Create a Shop model from array data
     * 
     * @param array $data Raw shop data from client
     * @return Shop
     */
    public static function createShop(array $data): Shop
    {
        return new Shop($data);
    }

    /**
     * Create a JPG model from array data
     * 
     * @param array $data Raw JPG data from client
     * @return JPG
     */
    public static function createJPG(array $data): JPG
    {
        return new JPG($data);
    }

    /**
     * Create multiple User models from array of data
     * 
     * @param array $users Array of user data arrays
     * @return User[]
     */
    public static function createUsers(array $users): array
    {
        return array_map([self::class, 'createUser'], $users);
    }

    /**
     * Create multiple Game models from array of data
     * 
     * @param array $games Array of game data arrays
     * @return Game[]
     */
    public static function createGames(array $games): array
    {
        return array_map([self::class, 'createGame'], $games);
    }

    /**
     * Create multiple Shop models from array of data
     * 
     * @param array $shops Array of shop data arrays
     * @return Shop[]
     */
    public static function createShops(array $shops): array
    {
        return array_map([self::class, 'createShop'], $shops);
    }

    /**
     * Create multiple JPG models from array of data
     * 
     * @param array $jpgs Array of JPG data arrays
     * @return JPG[]
     */
    public static function createJPGs(array $jpgs): array
    {
        return array_map([self::class, 'createJPG'], $jpgs);
    }

    /**
     * Convert a single model to array if needed (for backward compatibility)
     * 
     * @param object $model Any model object
     * @return array
     */
    public static function toArray($model): array
    {
        if ($model instanceof User) {
            return $model->getState();
        }
        
        if ($model instanceof Game) {
            return $model->getState();
        }
        
        if ($model instanceof Shop) {
            return $model->toArray();
        }
        
        if ($model instanceof JPG) {
            return $model->toArray();
        }
        
        // Fallback for other objects
        if (method_exists($model, 'toArray')) {
            return $model->toArray();
        }
        
        if (method_exists($model, 'getState')) {
            return $model->getState();
        }
        
        // Last resort - cast to array
        return (array) $model;
    }

    /**
     * Ensure an array of data contains only expected keys (data sanitization)
     * 
     * @param array $data Raw data array
     * @param array $allowedKeys List of allowed keys
     * @return array Sanitized data array
     */
    public static function sanitizeArray(array $data, array $allowedKeys): array
    {
        return array_intersect_key($data, array_flip($allowedKeys));
    }

    /**
     * Convert snake_case keys to camelCase (useful for client data normalization)
     * 
     * @param array $data Array with snake_case keys
     * @return array Array with camelCase keys
     */
    public static function snakeToCamel(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $result[$camelKey] = $value;
        }
        return $result;
    }

    /**
     * Convert camelCase keys to snake_case (useful for database compatibility)
     * 
     * @param array $data Array with camelCase keys
     * @return array Array with snake_case keys
     */
    public static function camelToSnake(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/([A-Z])/', '_$1', $key));
            $result[trim($snakeKey, '_')] = $value;
        }
        return $result;
    }
}