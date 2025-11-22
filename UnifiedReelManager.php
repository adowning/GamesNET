<?php

namespace Games;

class UnifiedReelManager
{
    private array $reelStrips;
    private array $bonusReelStrips;
    private GameReelConfig $config;

    public function __construct(array $reelData)
    {
        // Process all available reels dynamically
        $this->reelStrips = $this->extractReelStrips($reelData['reel_strips'], 'reelStrip');
        $this->bonusReelStrips = $this->extractReelStrips($reelData['reel_strips'], 'reelStripBonus');

        $this->config = new GameReelConfig($this->analyzeConfigFromData($reelData));
    }

    private function extractReelStrips(array $reelStrips, string $prefix): array
    {
        $extracted = [];
        foreach ($reelStrips as $key => $value) {
            if (str_starts_with($key, $prefix) && !empty($value)) {
                $extracted[$key] = $value;
            }
        }
        return $extracted;
    }

    private function analyzeConfigFromData(array $reelData): array
    {
        return [
            'hasBonusReels' => !empty($this->bonusReelStrips),
            'bonusReelCount' => count($this->bonusReelStrips),
            'reelCount' => count($this->reelStrips),
            'specialBonusTypes' => $this->detectSpecialBonusTypes($reelData['reel_strips']),
            'scatterSymbol' => $this->detectScatterSymbol($reelData),
            'outputPositions' => 3, // Default, configurable per game
            'positioningStrategy' => 'auto_detect' // Determine from game behavior
        ];
    }

    private function detectScatterSymbol(array $reelData): string
    {
        // Analyze reel strips to detect common scatter symbols
        // This is a simplified version - could be more sophisticated
        $symbolCounts = [];

        foreach ($this->reelStrips as $reelStrip) {
            foreach ($reelStrip as $symbol) {
                $symbolCounts[$symbol] = ($symbolCounts[$symbol] ?? 0) + 1;
            }
        }

        // Find symbols that appear frequently but not excessively
        // Common scatter symbols are often '0' or low numbers
        $candidates = ['0', '1', '2', '3', '4'];

        foreach ($candidates as $candidate) {
            if (isset($symbolCounts[$candidate]) && $symbolCounts[$candidate] > 10) {
                return (string)$candidate;
            }
        }

        // Fallback: return most common symbol that's not too common
        arsort($symbolCounts);
        foreach ($symbolCounts as $symbol => $count) {
            if ($count > 5 && $count < 50) { // Not too rare, not too common
                return (string)$symbol;
            }
        }

        return '0'; // Default fallback
    }

    private function detectSpecialBonusTypes(array $reelStrips): array
    {
        $types = [];
        foreach ($reelStrips as $key => $value) {
            if (str_contains($key, '_')) {
                $parts = explode('_', $key);
                if (count($parts) >= 3 && $parts[0] === 'reelStripBonus') {
                    $types[] = $parts[2]; // 'regular', 'expanding', etc.
                }
            }
        }
        return array_unique($types);
    }

    public function getReelStrips(string $winType, string $slotEvent, ?string $bonusType = null): array
    {
        // Select appropriate reel strips
        $activeReels = $this->selectActiveReels($winType, $slotEvent, $bonusType);

        // Generate positions using configured strategy
        $positions = $this->generatePositions($winType, $activeReels, $bonusType);

        // Build output format
        return $this->buildReelOutput($positions, $activeReels);
    }

    private function selectActiveReels(string $winType, string $slotEvent, ?string $bonusType): array
    {
        if ($slotEvent === 'freespin' && !empty($this->bonusReelStrips)) {
            // Handle different bonus reel types (TheWolfsBaneNET)
            if ($bonusType) {
                $reelNumber = $this->getReelNumber($bonusType);
                $typeKey = "reelStripBonus{$reelNumber}_{$bonusType}";

                // Try to find type-specific bonus reels first
                foreach ($this->bonusReelStrips as $key => $reelStrip) {
                    if (str_ends_with($key, "_{$bonusType}")) {
                        return [$key => $reelStrip];
                    }
                }

                // Fallback to general bonus reels
                return $this->bonusReelStrips;
            }
            return $this->bonusReelStrips;
        }

        return $this->reelStrips;
    }

    private function getReelNumber(string $bonusType): int
    {
        // Extract reel number from bonus type (e.g., "regular" -> 1, "expanding" -> 2, etc.)
        $typeMap = [
            'regular' => 1,
            'expanding' => 2,
            'spreading' => 3,
            'multiplier' => 4
        ];

        return $typeMap[$bonusType] ?? 1;
    }

    private function generatePositions(string $winType, array $reelStrips, ?string $bonusType = null): array
    {
        $positions = [];
        $strategy = $this->config->getPositioningStrategy();

        // Auto-detect strategy if not set
        if ($strategy === 'auto_detect') {
            $strategy = $this->detectPositioningStrategy($winType);
        }

        foreach ($reelStrips as $reelName => $reelStrip) {
            $reelNumber = (int) filter_var($reelName, FILTER_SANITIZE_NUMBER_INT);

            switch ($strategy) {
                case 'scatter_based':
                    $positions[$reelNumber] = $this->generateScatterBasedPosition($winType, $reelStrip, $reelNumber);
                    break;

                case 'fixed_reels':
                    $positions[$reelNumber] = $this->generateFixedReelPosition($winType, $reelStrip, $reelNumber);
                    break;

                case 'conditional':
                    $positions[$reelNumber] = $this->generateConditionalPosition($winType, $reelStrip, $reelNumber);
                    break;

                case 'random':
                default:
                    $positions[$reelNumber] = mt_rand(0, count($reelStrip) - 3);
                    break;
            }
        }

        return $positions;
    }

    private function detectPositioningStrategy(string $winType): string
    {
        // Simple heuristic to detect strategy based on game characteristics
        if ($this->config->hasSpecialBonusTypes()) {
            return 'scatter_based'; // Games with special bonus types often use scatter positioning
        }

        if (!empty($this->config->getFixedReelSelection())) {
            return 'fixed_reels';
        }

        if (!empty($this->config->getConditionalRules())) {
            return 'conditional';
        }

        return 'random'; // Default fallback
    }

    private function generateScatterBasedPosition(string $winType, array $reelStrip, int $reelNumber): int
    {
        if ($winType !== 'bonus') {
            return mt_rand(0, count($reelStrip) - 3);
        }

        $scatterSymbol = $this->config->getScatterSymbol();

        // Check if this reel should have scatter positioned
        if ($this->config->shouldPlaceScatter($reelNumber, count($reelStrip))) {
            $scatterPosition = $this->findScatterPosition($reelStrip, $scatterSymbol);
            if ($scatterPosition !== false) {
                return $scatterPosition;
            }
        }

        // Fallback to random positioning
        return mt_rand(0, count($reelStrip) - 3);
    }

    private function findScatterPosition(array $reelStrip, string $scatterSymbol): int|false
    {
        // Find positions where scatter symbol appears
        $scatterPositions = [];
        for ($i = 0; $i < count($reelStrip) - 2; $i++) {
            if ($reelStrip[$i] == $scatterSymbol) {
                $scatterPositions[] = $i;
            }
        }

        return !empty($scatterPositions)
            ? $scatterPositions[array_rand($scatterPositions)]
            : false;
    }

    private function generateFixedReelPosition(string $winType, array $reelStrip, int $reelNumber): int
    {
        // Use fixed reel selection if configured
        $fixedSelection = $this->config->getFixedReelSelection();
        if (in_array($reelNumber, $fixedSelection)) {
            // For fixed reels, use deterministic positioning
            // This could be based on game logic or just a fixed offset
            return (int)($reelNumber * 10) % (count($reelStrip) - 2);
        }

        return mt_rand(0, count($reelStrip) - 3);
    }

    private function generateConditionalPosition(string $winType, array $reelStrip, int $reelNumber): int
    {
        $conditionalRules = $this->config->getConditionalRules();

        foreach ($conditionalRules as $rule) {
            if (
                $rule['type'] === 'positioning' &&
                isset($rule['condition']) &&
                $rule['condition']($reelNumber, count($reelStrip))
            ) {

                if (isset($rule['action'])) {
                    return $rule['action']($reelStrip, $reelNumber);
                }
            }
        }

        return mt_rand(0, count($reelStrip) - 3);
    }

    private function buildReelOutput(array $positions, array $reelStrips): array
    {
        $reel = ['rp' => []];
        $outputPositions = $this->config->getOutputPositions();

        foreach ($positions as $index => $value) {
            $reelName = "reelStrip{$index}";
            $reelStrip = $reelStrips[$reelName] ?? [];

            if (empty($reelStrip)) continue;

            $cnt = count($reelStrip);
            $reelStrip[-1] = $reelStrip[$cnt - 1];
            $reelStrip[$cnt] = $reelStrip[0];

            // Build reel output positions
            for ($i = 0; $i < $outputPositions; $i++) {
                $reel["reel{$index}"][$i] = $reelStrip[$value + $i] ?? '';
            }

            $reel['rp'][] = $value;
        }

        return $reel;
    }

    public function getAllReelData(): array
    {
        return [
            'reelStrips' => $this->reelStrips,
            'bonusReelStrips' => $this->bonusReelStrips,
            'config' => $this->config->getConfig()
        ];
    }
}
