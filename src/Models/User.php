<?php

declare(strict_types=1);

namespace NationStates\Models;

use DateTime;

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private array $nations = [];
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private bool $isActive = true;

    public function __construct(
        string $username,
        string $email,
        string $password
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getNations(): array { return $this->nations; }
    public function isActive(): bool { return $this->isActive; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function addNation(Nation $nation): self
    {
        $this->nations[] = $nation;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->isActive = $active;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'nationsCount' => count($this->nations),
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
