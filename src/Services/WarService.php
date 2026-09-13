<?php

declare(strict_types=1);

namespace NationStates\Services;

use NationStates\Models\Nation;
use NationStates\Models\Military;
use NationStates\Models\Diplomacy;
use NationStates\Models\Economy;
use NationStates\Models\Event;
use DateTime;

class WarService
{
    public function simulateBattle(Nation $attacker, Nation $defender): array
    {
        $attackerMilitary = new Military($attacker->getId());
        $defenderMilitary = new Military($defender->getId());

        $attackerStrength = $attackerMilitary->getTotalMilitaryStrength();
        $defenderStrength = $defenderMilitary->getTotalMilitaryStrength();

        // Determine winner
        $attackerWins = $attackerStrength > $defenderStrength;
        
        // Calculate casualties
        $strengthDifference = abs($attackerStrength - $defenderStrength);
        $casualtyRate = min(0.5, $strengthDifference / max($attackerStrength, $defenderStrength, 1));

        $attackerCasualties = (int)($attackerMilitary->getArmySize() * $casualtyRate);
        $defenderCasualties = (int)($defenderMilitary->getArmySize() * $casualtyRate);

        // Update military forces
        if ($attackerWins) {
            $attackerMilitary->recordVictory();
            $defenderMilitary->recordBattle($defenderCasualties);
            $winner = $attacker->getName();
        } else {
            $defenderMilitary->recordVictory();
            $attackerMilitary->recordBattle($attackerCasualties);
            $winner = $defender->getName();
        }

        return [
            'winner' => $winner,
            'loser' => $attackerWins ? $defender->getName() : $attacker->getName(),
            'attackerCasualties' => $attackerCasualties,
            'defenderCasualties' => $defenderCasualties,
            'totalCasualties' => $attackerCasualties + $defenderCasualties,
            'casualtyRate' => round($casualtyRate * 100, 2),
            'attackerStrength' => round($attackerStrength, 2),
            'defenderStrength' => round($defenderStrength, 2),
        ];
    }

    public function calculateConqueredTerritories(Nation $winner, Nation $loser): float
    {
        $winnerStrength = 1.5; // Winner advantage
        $terrainLost = $loser->getLandArea() * 0.1 * $winnerStrength;
        return min($terrainLost, $loser->getLandArea() * 0.5); // Max 50% of territory
    }

    public function generateWarEvent(Nation $attacker, Nation $defender): Event
    {
        $event = new Event(
            "War: {$attacker->getName()} vs {$defender->getName()}",
            "{$attacker->getName()} has declared war on {$defender->getName()}!",
            'war',
            $attacker->getId()
        );
        
        return $event;
    }
}
