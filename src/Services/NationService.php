<?php

declare(strict_types=1);

namespace NationStates\Services;

use NationStates\Models\Nation;
use DateTime;

class NationService
{
    private array $nations = [];

    public function createNation(string $name, int $userId): Nation
    {
        $slug = $this->generateSlug($name);
        $nation = new Nation($name, $slug, $userId);
        $this->nations[$slug] = $nation;
        return $nation;
    }

    public function getNation(string $slug): ?Nation
    {
        return $this->nations[$slug] ?? null;
    }

    public function getAllNations(): array
    {
        return array_values($this->nations);
    }

    public function getNationsByUser(int $userId): array
    {
        return array_filter($this->nations, fn($n) => $n->getUserId() === $userId);
    }

    public function updateNation(Nation $nation): Nation
    {
        $this->nations[$nation->getSlug()] = $nation;
        return $nation;
    }

    public function deleteNation(string $slug): bool
    {
        if (isset($this->nations[$slug])) {
            unset($this->nations[$slug]);
            return true;
        }
        return false;
    }

    public function simulateDailyUpdate(Nation $nation): Nation
    {
        // Tax collection
        $taxes = $nation->getTotalTaxes();
        $nation->addTreasury($taxes);

        // Population growth (affected by happiness)
        $growthRate = 0.002 * $nation->getHappiness();
        $newPopulation = (int)($nation->getPopulation() * (1 + $growthRate));
        $nation->setPopulation($newPopulation);

        // Happiness changes (affected by economy and government)
        $economicFactor = min($nation->getTreasury() / 100000, 0.1);
        $newHappiness = $nation->getHappiness() + ($economicFactor * 0.01) - 0.005;
        $nation->setHappiness($newHappiness);

        // GDP growth
        $gdpGrowth = $nation->getGdp() * (0.02 * $nation->getHappiness());
        $nation->gdp += $gdpGrowth;

        // Influence decay if not maintained
        if ($nation->getInfluence() > 0) {
            $nation->influence = max(0, $nation->getInfluence() - 0.1);
        }

        $nation->setLastUpdate();
        return $this->updateNation($nation);
    }

    public function declareWar(Nation $attacker, Nation $defender): array
    {
        $attackerStrength = $attacker->getMilitaryStrength();
        $defenderStrength = $defender->getMilitaryStrength();

        $attackerWin = $attackerStrength > $defenderStrength;
        $casualtyRate = abs($attackerStrength - $defenderStrength) / max($attackerStrength, $defenderStrength);

        if ($attackerWin) {
            $defenderCasualties = (int)($defender->getArmySize() * $casualtyRate);
            $defender->armySize = max(0, $defender->getArmySize() - $defenderCasualties);
            $attacker->influence += 5;
        } else {
            $attackerCasualties = (int)($attacker->getArmySize() * $casualtyRate);
            $attacker->armySize = max(0, $attacker->getArmySize() - $attackerCasualties);
            $defender->influence += 5;
        }

        return [
            'winner' => $attackerWin ? $attacker->getName() : $defender->getName(),
            'casualtyRate' => round($casualtyRate * 100, 2),
        ];
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'nation-' . time();
    }
}
