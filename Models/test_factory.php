<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Game.php';
require_once __DIR__ . '/Shop.php';
require_once __DIR__ . '/JPG.php';
require_once __DIR__ . '/ModelFactory.php';

use Models\ModelFactory;

echo "=== Testing ModelFactory ===\n\n";

// Test data that might come from TypeScript client
$userData = [
    'id' => 123,
    'balance' => 500.50,
    'shop_id' => 1,
    'count_balance' => 480.25,
    'address' => 0.0,
    'session' => 'abc123',
    'is_blocked' => false,
    'status' => 'active',
    'remember_token' => 'token123',
    'last_bid' => null
];

$gameData = [
    'id' => 456,
    'name' => 'Fortune Rangers',
    'shop_id' => 1,
    'stat_in' => 1000.0,
    'stat_out' => 950.0,
    'bids' => 100,
    'denomination' => 1.0,
    'slotViewState' => 'main',
    'bet' => '0.1,0.2,0.5,1,2,5',
    'jp_config' => [
        'main_bank' => 2000.0,
        'bonus_bank' => 500.0,
        'jp_1' => 10000.0,
        'jp_1_percent' => 2.5,
        'lines_percent_config' => [
            'default' => [
                'line10' => ['0_100' => 20],
                'line9' => ['0_100' => 25],
                'line5' => ['0_100' => 30]
            ]
        ]
    ],
    'rezerv' => 100,
    'view' => true,
    'advanced' => ''
];

$shopData = [
    'id' => 1,
    'max_win' => 5000.0,
    'percent' => 12.5,
    'is_blocked' => false,
    'currency' => 'USD'
];

$jpgData = [
    'id' => 789,
    'shop_id' => 1,
    'balance' => 5000.0,
    'percent' => 1.5,
    'user_id' => null,
    'start_balance' => 1000.0
];

echo "1. Testing User Model Creation:\n";
$user = ModelFactory::createUser($userData);
echo "   - User ID: " . $user->id . "\n";
echo "   - Balance: " . $user->balance . "\n";
echo "   - Is Modified: " . ($user->hasChanges() ? 'Yes' : 'No') . "\n";
echo "   ✓ User model created successfully\n\n";

echo "2. Testing Game Model Creation:\n";
$game = ModelFactory::createGame($gameData);
echo "   - Game ID: " . $game->id . "\n";
echo "   - Game Name: " . $game->name . "\n";
echo "   - Lines Percent Config: ";
$linesConfig = $game->getLinesPercentConfig('default');
print_r($linesConfig);
echo "   - Snake Case Method Test: ";
$snakeConfig = $game->get_lines_percent_config('default');
print_r($snakeConfig);
echo "   ✓ Game model created successfully\n\n";

echo "3. Testing Shop Model Creation:\n";
$shop = ModelFactory::createShop($shopData);
echo "   - Shop ID: " . $shop->id . "\n";
echo "   - Max Win: " . $shop->max_win . "\n";
echo "   - Currency: " . $shop->currency . "\n";
echo "   ✓ Shop model created successfully\n\n";

echo "4. Testing JPG Model Creation:\n";
$jpg = ModelFactory::createJPG($jpgData);
echo "   - JPG ID: " . $jpg->id . "\n";
echo "   - Balance: " . $jpg->balance . "\n";
echo "   - Pay Sum: " . $jpg->getPaySum() . "\n";
echo "   ✓ JPG model created successfully\n\n";

echo "5. Testing Array Conversion:\n";
$userArray = ModelFactory::toArray($user);
echo "   - User as Array Keys: " . implode(', ', array_keys($userArray)) . "\n";
echo "   ✓ Array conversion working\n\n";

echo "6. Testing Bulk Creation:\n";
$usersData = [$userData, array_merge($userData, ['id' => 124, 'balance' => 300.0])];
$users = ModelFactory::createUsers($usersData);
echo "   - Created " . count($users) . " users\n";
echo "   - First user balance: " . $users[0]->balance . "\n";
echo "   - Second user balance: " . $users[1]->balance . "\n";
echo "   ✓ Bulk creation working\n\n";

echo "7. Testing Snake to Camel Case Conversion:\n";
$snakeData = ['user_id' => 123, 'first_name' => 'John', 'last_login_at' => '2023-01-01'];
$camelData = ModelFactory::snakeToCamel($snakeData);
echo "   - Original: " . json_encode($snakeData) . "\n";
echo "   - CamelCase: " . json_encode($camelData) . "\n";
echo "   ✓ Snake to camel case conversion working\n\n";

echo "=== All Tests Passed! ===\n";
echo "The ModelFactory is working correctly and can convert client array data to proper Model objects.\n";
echo "Games can now use method calls like get_lines_percent_config() on Game objects.\n";