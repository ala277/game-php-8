<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;

class Nation
{
    private int $id;
    private string $name;
    private string $slug;
    private int $userId;
    private string $government = 'Democracy';
    private string $currency = 'Dollar';
    
    // Economy
    private float $gdp = 1000000.0;
    private float $treasury = 50000.0;
    private float $taxRate = 0.20;
    
    // Population & Land
    private int $population = 1000000;
    private float $landArea = 1000.0; // km²
    public float $happiness = 0.7;
    
    // Military
    public int $armySize = 1000;
    public int $navySize = 100;
    public int $airForceSize = 50;
    
    // Influence & Diplomacy
    public float $influence = 0.0;
    private int $alliesCount = 0;
    private int $enemiesCount = 0;
    
    // Statistics
    private float $literacy = 0.9;
    private float $lifeExpectancy = 78.0;
    private int $crimesPerDay = 100;
    
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $lastUpdate = null;

    public function __construct(
        string $name,
        string $slug,
        int $userId
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->userId = $userId;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getUserId(): int { return $this->userId; }
    public function getGovernment(): string { return $this->government; }
    public function getCurrency(): string { return $this->currency; }
    public function getGdp(): float { return $this->gdp; }
    public function getTreasury(): float { return $this->treasury; }
    public function getTaxRate(): float { return $this->taxRate; }
    public function getPopulation(): int { return $this->population; }
    public function getLandArea(): float { return $this->landArea; }
    public function getHappiness(): float { return $this->happiness; }
    public function getArmySize(): int { return $this->armySize; }
    public function getNavySize(): int { return $this->navySize; }
    public function getAirForceSize(): int { return $this->airForceSize; }
    public function getInfluence(): float { return $this->influence; }
    public function getAlliesCount(): int { return $this->alliesCount; }
    public function getEnemiesCount(): int { return $this->enemiesCount; }
    public function getLiteracy(): float { return $this->literacy; }
    public function getLifeExpectancy(): float { return $this->lifeExpectancy; }
    public function getCrimesPerDay(): int { return $this->crimesPerDay; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    // Setters
    public function setGovernment(string $government): self { $this->government = $government; return $this; }
    public function setCurrency(string $currency): self { $this->currency = $currency; return $this; }
    public function setTaxRate(float $rate): self { 
        $this->taxRate = max(0, min(1, $rate)); 
        return $this; 
    }
    public function setPopulation(int $population): self { 
        $this->population = max(1, $population); 
        return $this; 
    }
    public function setLandArea(float $area): self { 
        $this->landArea = max(0.1, $area); 
        return $this; 
    }
    public function setHappiness(float $happiness): self { 
        $this->happiness = max(0, min(1, $happiness)); 
        return $this; 
    }

    // Business Logic
    public function addTreasury(float $amount): self
    {
        $this->treasury = max(0, $this->treasury + $amount);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getPopulationDensity(): float
    {
        return $this->landArea > 0 ? $this->population / $this->landArea : 0;
    }

    public function getMilitaryStrength(): float
    {
        return ($this->armySize * 1.0) + ($this->navySize * 1.5) + ($this->airForceSize * 1.2);
    }

    public function getTotalTaxes(): float
    {
        return $this->gdp * $this->taxRate;
    }

    public function recruitArmy(int $count): bool
    {
        $cost = $count * 100;
        if ($this->treasury >= $cost) {
            $this->armySize += $count;
            $this->addTreasury(-$cost);
            return true;
        }
        return false;
    }

    public function setLastUpdate(): self
    {
        $this->lastUpdate = new DateTime();
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'government' => $this->government,
            'gdp' => round($this->gdp, 2),
            'treasury' => round($this->treasury, 2),
            'population' => $this->population,
            'landArea' => $this->landArea,
            'happiness' => round($this->happiness, 2),
            'military' => [
                'army' => $this->armySize,
                'navy' => $this->navySize,
                'airForce' => $this->airForceSize,
                'strength' => round($this->getMilitaryStrength(), 2),
            ],
            'influence' => round($this->influence, 2),
            'stats' => [
                'literacy' => round($this->literacy, 2),
                'lifeExpectancy' => round($this->lifeExpectancy, 2),
                'populationDensity' => round($this->getPopulationDensity(), 2),
            ],
        ];
    }
}
