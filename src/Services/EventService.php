<?php

declare(strict_types=1);

namespace NationStates\Services;

use NationStates\Models\Event;
use NationStates\Models\Nation;
use DateTime;

class EventService
{
    private array $events = [];
    private array $eventTypes = [
        'natural_disaster' => [
            'Earthquake struck the nation',
            'Severe drought affecting agriculture',
            'Major flood in the country',
            'Tsunami warning issued',
        ],
        'election' => [
            'Presidential election held',
            'Parliament dissolved',
            'New government formed',
            'Peaceful power transition',
        ],
        'economy' => [
            'Stock market crash',
            'Economic boom detected',
            'Currency devalued',
            'Trade deal signed',
        ],
        'social' => [
            'Protests in capital city',
            'Labor strike concluded',
            'New laws passed',
            'Cultural festival celebrated',
        ],
    ];

    public function createRandomEvent(Nation $nation): Event
    {
        $typeKeys = array_keys($this->eventTypes);
        $type = $typeKeys[array_rand($typeKeys)];
        $messages = $this->eventTypes[$type];
        $message = $messages[array_rand($messages)];

        $event = new Event(
            ucfirst($type) . ' Event',
            $message,
            $type,
            $nation->getId()
        );

        // Set random impact
        $event->setImpact((mt_rand(-50, 50) / 100));

        $this->events[$event->getId()] = $event;
        return $event;
    }

    public function getEventsByNation(int $nationId): array
    {
        return array_filter($this->events, fn($e) => $e->getNationId() === $nationId);
    }

    public function getEventsByType(string $type): array
    {
        return array_filter($this->events, fn($e) => $e->getType() === $type);
    }

    public function getAllEvents(): array
    {
        return array_values($this->events);
    }

    public function deleteOldEvents(int $days = 30): int
    {
        $cutoffDate = new DateTime("-{$days} days");
        $deletedCount = 0;

        foreach ($this->events as $id => $event) {
            if ($event->getOccuredAt() < $cutoffDate) {
                unset($this->events[$id]);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
