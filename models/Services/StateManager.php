<?php
namespace Services;

use Models\User;
use Models\Game;
use Models\JPG;
use Models\GameBank;

class StateManager
{
    private array $models = [];
    
    public function registerModel(string $type, object $model): void
    {
        $this->models[$type] = $model;
    }
    
    public function getChangedModels(): array
    {
        $changedModels = [];
        
        foreach ($this->models as $type => $model) {
            if (method_exists($model, 'hasChanges') && $model->hasChanges()) {
                $changedModels[$type] = $model->getChanges();
            }
        }
        
        return $changedModels;
    }
    
    public function getAllStates(): array
    {
        $states = [];
        
        foreach ($this->models as $type => $model) {
            if (method_exists($model, 'getState')) {
                $states[$type] = $model->getState();
            }
        }
        
        return $states;
    }
    
    public function markAllForSave(): void
    {
        foreach ($this->models as $model) {
            if (method_exists($model, 'save')) {
                $model->save();
            }
        }
    }
}
