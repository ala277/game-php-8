<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;
use JsonSerializable;

class Economy implements JsonSerializable
{
    private int $nationId;
    private float $gdp = 1000000.0;
    private float $perCapitaIncome = 1000.0;
    private float $treasury = 50000.0;
    private float $taxRate = 0.20;
    private float $unemployment = 0.05;
    private float $inflation = 0.02;
    private array $sectors = [
        'agriculture' => 0.15,
        'manufacturing' => 0.25,
        'services' => 0.40,
        'technology' => 0.10,
        'tourism' => 0.10,
    ];
    private float $tradeBalance = 0.0;
    private float $foreignDebt = 0.0;
    private DateTime $updatedAt;

    public function __construct(int $nationId)
    {
        $this->nationId = $nationId;
        $this->updatedAt = new DateTime();
    }

    public function getNationId(): int { return $this->nationId; }
    public function getGdp(): float { return $this->gdp; }
    public function getPerCapitaIncome(): float { return $this->perCapitaIncome; }
    public function getTreasury(): float { return $this->treasury; }
    public function getTaxRate(): float { return $this->taxRate; }
    public function getUnemployment(): float { return $this->unemployment; }
    public function getInflation(): float { return $this->inflation; }
    public function getSectors(): array { return $this->sectors; }
    public function getTradeBalance(): float { return $this->tradeBalance; }
    public function getForeignDebt(): float { return $this->foreignDebt; }

    public function setTaxRate(float $rate): self
    {
        $this->taxRate = max(0, min(1, $rate));
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function collectTaxes(int $population): self
    {
        $taxes = $this->gdp * $this->taxRate;
        $this->treasury += $taxes;
        
        // تأثير معدل الضرائب على البطالة
        if ($this->taxRate > 0.35) {
            $this->unemployment = min(0.2, $this->unemployment + 0.005);
        } elseif ($this->taxRate < 0.15) {
            $this->unemployment = max(0, $this->unemployment - 0.005);
        }
        
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function growEconomy(float $growthRate): self
    {
        $growth = $this->gdp * $growthRate;
        $this->gdp += $growth;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function spendFromTreasury(float $amount): bool
    {
        if ($this->treasury >= $amount) {
            $this->treasury -= $amount;
            $this->updatedAt = new DateTime();
            return true;
        }
        return false;
    }

    public function investInSector(string $sector, float $amount): bool
    {
        if (!isset($this->sectors[$sector])) {
            return false;
        }

        if ($this->spendFromTreasury($amount)) {
            $growth = $amount / 100000; // تحويل الاستثمار إلى نسبة نمو
            $this->sectors[$sector] += $growth * 0.1;
            $this->gdp *= (1 + ($growth * 0.05));
            return true;
        }
        return false;
    }

    public function createTrade(float $volume): self
    {
        $this->tradeBalance += $volume;
        $this->gdp *= 1.01; // نمو طفيف من التجارة
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function takeLoan(float $amount): self
    {
        $this->treasury += $amount;
        $this->foreignDebt += $amount * 1.05; // فائدة 5%
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function payDebt(float $amount): bool
    {
        if ($this->spendFromTreasury($amount)) {
            $this->foreignDebt = max(0, $this->foreignDebt - $amount);
            return true;
        }
        return false;
    }

    public function simulateDailyEconomy(int $population): self
    {
        // تحديث الدخل الفردي
        $this->perCapitaIncome = $population > 0 ? $this->gdp / $population : 0;
        
        // نمو اقتصادي عشوائي
        $randomGrowth = (mt_rand(-3, 5) / 100);
        $this->growEconomy($randomGrowth);
        
        // تأثر التضخم
        $this->inflation = max(0, $this->inflation + (mt_rand(-1, 2) / 100));
        
        // جمع الضرائب
        $this->collectTaxes($population);
        
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'gdp' => round($this->gdp, 2),
            'perCapitaIncome' => round($this->perCapitaIncome, 2),
            'treasury' => round($this->treasury, 2),
            'taxRate' => round($this->taxRate * 100, 2) . '%',
            'unemployment' => round($this->unemployment * 100, 2) . '%',
            'inflation' => round($this->inflation * 100, 2) . '%',
            'sectors' => array_map(fn($v) => round($v * 100, 2) . '%', $this->sectors),
            'tradeBalance' => round($this->tradeBalance, 2),
            'foreignDebt' => round($this->foreignDebt, 2),
        ];
    }
}
