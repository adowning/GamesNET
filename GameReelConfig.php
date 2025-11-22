<?php

namespace Games;

class GameReelConfig
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    private function getDefaultConfig(): array
    {
        return [
            // Reel configuration
            'reelCount' => 5,
            'bonusReelCount' => 0,
            'hasBonusReels' => false,

            // Output configuration
            'outputPositions' => 3,
            'positionOutput' => [0, 1, 2], // Standard 3-position output

            // Symbol configuration
            'scatterSymbol' => '0',
            'wildSymbol' => null,

            // Positioning strategy
            'positioningStrategy' => 'random',
            'scatterPositioning' => true,
            'fixedReelSelection' => [],
            'conditionalRules' => [],

            // Bonus configuration
            'specialBonusTypes' => [],
            'bonusTypeParameter' => false, // Whether GetReelStrips accepts bonus type

            // Game-specific rules
            'reelSpinningDirection' => 'forward', // or 'reverse'
            'symbolSubstitution' => true,
            'progressiveJackpot' => false,
        ];
    }

    // Getters
    public function getReelCount(): int
    {
        return $this->config['reelCount'];
    }

    public function getBonusReelCount(): int
    {
        return $this->config['bonusReelCount'];
    }

    public function hasBonusReels(): bool
    {
        return $this->config['hasBonusReels'];
    }

    public function getOutputPositions(): int
    {
        return $this->config['outputPositions'];
    }

    public function getScatterSymbol(): string
    {
        return $this->config['scatterSymbol'];
    }

    public function getPositioningStrategy(): string
    {
        return $this->config['positioningStrategy'];
    }

    public function getFixedReelSelection(): array
    {
        return $this->config['fixedReelSelection'];
    }

    public function getConditionalRules(): array
    {
        return $this->config['conditionalRules'];
    }

    public function hasSpecialBonusTypes(): bool
    {
        return !empty($this->config['specialBonusTypes']);
    }

    public function getSpecialBonusTypes(): array
    {
        return $this->config['specialBonusTypes'];
    }

    public function acceptsBonusTypeParameter(): bool
    {
        return $this->config['bonusTypeParameter'];
    }

    // Configuration methods
    public function setScatterSymbol(string $symbol): void
    {
        $this->config['scatterSymbol'] = $symbol;
    }

    public function setPositioningStrategy(string $strategy): void
    {
        $this->config['positioningStrategy'] = $strategy;
    }

    public function setFixedReelSelection(array $reels): void
    {
        $this->config['fixedReelSelection'] = $reels;
    }

    public function setConditionalRules(array $rules): void
    {
        $this->config['conditionalRules'] = $rules;
    }

    public function setBonusTypeParameter(bool $enabled): void
    {
        $this->config['bonusTypeParameter'] = $enabled;
    }

    // Strategy-specific configuration
    public function isScatterBased(): bool
    {
        return $this->config['positioningStrategy'] === 'scatter_based';
    }

    public function isFixedReel(): bool
    {
        return $this->config['positioningStrategy'] === 'fixed_reels';
    }

    public function isRandom(): bool
    {
        return $this->config['positioningStrategy'] === 'random';
    }

    public function isConditional(): bool
    {
        return $this->config['positioningStrategy'] === 'conditional';
    }

    // Utility methods
    public function shouldPlaceScatter(int $reelNumber, int $totalReels): bool
    {
        if (!$this->config['scatterPositioning']) {
            return false;
        }

        // Apply conditional rules if any
        foreach ($this->config['conditionalRules'] as $rule) {
            if (
                $rule['type'] === 'scatter_placement' &&
                $rule['reel'] === $reelNumber &&
                $rule['condition']($reelNumber, $totalReels)
            ) {
                return $rule['action'] === 'place';
            }
        }

        // Default scatter placement logic
        return match ($this->config['positioningStrategy']) {
            'scatter_based' => $this->shouldPlaceScatterByStrategy($reelNumber, $totalReels),
            'fixed_reels' => in_array($reelNumber, $this->config['fixedReelSelection']),
            default => false
        };
    }

    private function shouldPlaceScatterByStrategy(int $reelNumber, int $totalReels): bool
    {
        // This would contain the specific scatter placement logic
        // For example, StarBurstNET places scatter on reels 1,2,3,4,5
        // SpaceWarsNET might have different logic

        if (empty($this->config['scatterPlacementRules'])) {
            return true; // Default: place scatter on all eligible reels
        }

        foreach ($this->config['scatterPlacementRules'] as $rule) {
            if ($rule['reel'] === $reelNumber) {
                return $rule['place'] ?? true;
            }
        }

        return false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
