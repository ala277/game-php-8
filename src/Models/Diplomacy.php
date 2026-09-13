<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;
use JsonSerializable;

class Diplomacy implements JsonSerializable
{
    private int $nationId;
    private array $alliances = [];
    private array $enemies = [];
    private array $tradeAgreements = [];
    private float $influence = 0.0;
    private float $reputation = 0.5;
    private int $diplomaticVictories = 0;
    private DateTime $updatedAt;

    public function __construct(int $nationId)
    {
        $this->nationId = $nationId;
        $this->updatedAt = new DateTime();
    }

    public function getNationId(): int { return $this->nationId; }
    public function getAlliances(): array { return $this->alliances; }
    public function getEnemies(): array { return $this->enemies; }
    public function getInfluence(): float { return $this->influence; }
    public function getReputation(): float { return $this->reputation; }
    public function getDiplomaticVictories(): int { return $this->diplomaticVictories; }

    public function formAlliance(int $allyId): bool
    {
        if (!in_array($allyId, $this->alliances)) {
            $this->alliances[] = $allyId;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function breakAlliance(int $allyId): bool
    {
        $key = array_search($allyId, $this->alliances);
        if ($key !== false) {
            unset($this->alliances[$key]);
            $this->alliances = array_values($this->alliances);
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function declareEnemy(int $enemyId): bool
    {
        if (!in_array($enemyId, $this->enemies)) {
            $this->enemies[] = $enemyId;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function makeWith(int $enemyId): bool
    {
        $key = array_search($enemyId, $this->enemies);
        if ($key !== false) {
            unset($this->enemies[$key]);
            $this->enemies = array_values($this->enemies);
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function signTradeAgreement(int $partnerId, float $volume): bool
    {
        $this->tradeAgreements[$partnerId] = $volume;
        $this->influence += 1.0;
        $this->updatedAt = new DateTime();
        return true;
    }

    public function addInfluence(float $amount): self
    {
        $this->influence = max(0, $this->influence + $amount);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function updateReputation(float $change): self
    {
        $this->reputation = max(0, min(1, $this->reputation + $change));
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function recordDiplomaticVictory(): self
    {
        $this->diplomaticVictories++;
        $this->influence += 5.0;
        $this->reputation = min(1, $this->reputation + 0.05);
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getTotalTradeVolume(): float
    {
        return array_sum($this->tradeAgreements);
    }

    public function jsonSerialize(): array
    {
        return [
            'allies' => $this->alliances,
            'enemies' => $this->enemies,
            'alliesCount' => count($this->alliances),
            'enemiesCount' => count($this->enemies),
            'influence' => round($this->influence, 2),
            'reputation' => round($this->reputation, 2),
            'diplomaticVictories' => $this->diplomaticVictories,
            'tradeAgreements' => count($this->tradeAgreements),
            'totalTradeVolume' => round($this->getTotalTradeVolume(), 2),
        ];
    }
}
