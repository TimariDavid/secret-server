<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SecretRepository::class)]
class Secret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['secret:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['secret:read'])]
    private string $hash;

    #[ORM\Column(type: 'text')]
    #[Groups(['secret:read'])]
    private string $secretText;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['secret:read'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['secret:read'])]
    private ?DateTimeInterface $expiresAt = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['secret:read'])]
    private int $remainingViews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getSecretText(): string
    {
        return $this->secretText;
    }

    public function setSecretText(string $secretText): void
    {
        $this->secretText = $secretText;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getRemainingViews(): int
    {
        return $this->remainingViews;
    }

    public function setRemainingViews(int $remainingViews): void
    {
        $this->remainingViews = $remainingViews;
    }

    /**
     * Returns the accepted headers.
     *
     * @return array Accepted header types
     */
    public static function getAcceptedHeaderTypes(): array
    {
        return [
            'application/json',
            'application/xml',
            'application/x-yaml'
        ];
    }

    /**
     * Returns the serializer format from the header type.
     *
     * @param string $headerType Header type
     *
     * @return string Serializer format
     */
    public static function getSerializerFormat(string $headerType): string
    {
        return match ($headerType) {
            'application/xml' => 'xml',
            'application/x-yaml' => 'yaml',
            default => 'json'
        };
    }
}
