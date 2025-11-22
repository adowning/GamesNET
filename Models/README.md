# Stateless Models for Casino Games

This directory contains zero-dependency model classes designed for stateless server operations.

## Overview

These models are designed to work in a stateless architecture where:
1. Models are instantiated from JSON data received from TypeScript server
2. All calculations happen without database calls
3. Changes are tracked and returned to calling server for persistence
4. No Laravel or framework dependencies

## Models

### Core Models
- **User.php** - User data with change tracking
- **Game.php** - Game configuration and statistics
- **Shop.php** - Shop configuration
- **JPG.php** - Jackpot management
- **GameBank.php** - Game bank operations

### Supporting Models
- **GameLog.php** - Game logging
- **StatGame.php** - Game statistics
- **Session.php** - Session management
- **Banker.php** - Static bank operations
- **UserStatus.php** - User status constants

### Services
- **StateManager.php** - Tracks model changes and provides state data

## Usage

```php
// Include autoloader
require_once __DIR__ . '/models/autoload.php';

// Create models from JSON data
$userData = json_decode($requestData, true)['user'];
$gameData = json_decode($requestData, true)['game'];

$user = new Models\User($userData);
$game = new Models\Game($gameData);

// Make changes (these are tracked)
$user->increment('balance', 50.0);
$game->increment('bids', 1);

// Get changed data for persistence
$stateManager = new Services\StateManager();
$stateManager->registerModel('user', $user);
$stateManager->registerModel('game', $game);

$changedStates = $stateManager->getChangedModels();
// Send $changedStates back to TypeScript server for database updates
