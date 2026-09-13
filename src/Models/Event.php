<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;
use JsonSerializable;

class Event implements JsonSerializable
{
    private string $id;
    private string $title;
    private string $description;
    private string $type; // war, election, natural_disaster, etc.
    private int $nationId;
    private float $impact = 0.0;
    private DateTime $occuredAt;
    private array $choices = [];

    public function __construct(
        string $title,
        string $description,
        string $type,
        int $nationId
    ) {
        $this->id = uniqid('event_', true);
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->nationId = $nationId;
        $this->occuredAt = new DateTime();
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): string { return $this->type; }
    public function getNationId(): int { return $this->nationId; }
    public function getImpact(): float { return $this->impact; }
    public function getOccuredAt(): DateTime { return $this->occuredAt; }
    public function getChoices(): array { return $this->choices; }

    public function setImpact(float $impact): self
    {
        $this->impact = $impact;
        return $this;
    }

    public function addChoice(string $title, callable $callback): self
    {
        $this->choices[] = [
            'title' => $title,
            'callback' => $callback,
        ];
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'impact' => round($this->impact, 2),
            'occuredAt' => $this->occuredAt->format('Y-m-d H:i:s'),
            'choicesCount' => count($this->choices),
        ];
    }
}
