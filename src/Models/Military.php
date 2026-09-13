<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;
use JsonSerializable;

class Military implements JsonSerializable
{
    private int $id;
    private int $nationId;
    private int $armySize = 1000;
    private int $navySize = 100;
    private int $airForceSize = 50;
    private int $armyLevel = 1;
    private int $navyLevel = 1;
    private int $airForceLevel = 1;
    private float $militaryBudget = 50000.0;
    private float $morale = 0.8;
    private int $casualties = 0;
    private DateTime $lastBattle;
    private DateTime $updatedAt;

    public function __construct(int $nationId)
    {
        $this->id = $nationId;
        $this->nationId = $nationId;
        $this->lastBattle = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): int { return $this->id; }
    public function getNationId(): int { return $this->nationId; }
    public function getArmySize(): int { return $this->armySize; }
    public function getNavySize(): int { return $this->navySize; }
    public function getAirForceSize(): int { return $this->airForceSize; }
    public function getMilitaryBudget(): float { return $this->militaryBudget; }
    public function getMorale(): float { return $this->morale; }
    public function getCasualties(): int { return $this->casualties; }
    public function getLastBattle(): DateTime { return $this->lastBattle; }

    public function setArmySize(int $size): self
    {
        $this->armySize = max(0, $size);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function setNavySize(int $size): self
    {
        $this->navySize = max(0, $size);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function setAirForceSize(int $size): self
    {
        $this->airForceSize = max(0, $size);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getTotalMilitaryStrength(): float
    {
        $strength = ($this->armySize * 1.0) + ($this->navySize * 1.5) + ($this->airForceSize * 1.2);
        return $strength * (0.5 + ($this->morale * 0.5));
    }

    public function recruitArmy(int $count, float $cost = 100.0): bool
    {
        $totalCost = $count * $cost;
        if ($this->militaryBudget >= $totalCost) {
            $this->armySize += $count;
            $this->militaryBudget -= $totalCost;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function recruitNavy(int $count, float $cost = 500.0): bool
    {
        $totalCost = $count * $cost;
        if ($this->militaryBudget >= $totalCost) {
            $this->navySize += $count;
            $this->militaryBudget -= $totalCost;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function recruitAirForce(int $count, float $cost = 1000.0): bool
    {
        $totalCost = $count * $cost;
        if ($this->militaryBudget >= $totalCost) {
            $this->airForceSize += $count;
            $this->militaryBudget -= $totalCost;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function addMorale(float $amount): self
    {
        $this->morale = max(0, min(1, $this->morale + $amount));
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function recordBattle(int $casualties): self
    {
        $this->casualties += $casualties;
        $this->lastBattle = new DateTime();
        $this->morale = max(0.3, $this->morale - 0.1);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function recordVictory(): self
    {
        $this->morale = min(1, $this->morale + 0.15);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function upgradeMilitary(): self
    {
        $upgradeCost = 10000;
        if ($this->militaryBudget >= $upgradeCost) {
            $this->armyLevel++;
            $this->navyLevel++;
            $this->airForceLevel++;
            $this->militaryBudget -= $upgradeCost;
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'army' => [
                'size' => $this->armySize,
                'level' => $this->armyLevel,
            ],
            'navy' => [
                'size' => $this->navySize,
                'level' => $this->navyLevel,
            ],
            'airForce' => [
                'size' => $this->airForceSize,
                'level' => $this->airForceLevel,
            ],
            'budget' => round($this->militaryBudget, 2),
            'morale' => round($this->morale, 2),
            'totalStrength' => round($this->getTotalMilitaryStrength(), 2),
            'casualties' => $this->casualties,
            'lastBattle' => $this->lastBattle->format('Y-m-d H:i:s'),
        ];
    }
}
