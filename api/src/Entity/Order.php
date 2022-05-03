<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Enum\Status;
use App\Repository\OrderRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        'get' => ['security' => 'is_granted("ROLE_ADMIN")'],
    ],
    itemOperations: [
        'get' => ['security' => 'is_granted("ROLE_ADMIN") or object.owner == user'],
        'put' => ['security' => 'is_granted("ROLE_ADMIN")'],
        'delete' => ['security' => 'is_granted("ROLE_ADMIN")'],
    ],
    attributes: ['security' => 'is_granted("ROLE_USER")'],
    denormalizationContext: ['groups' => ['order:write']],
    normalizationContext: ['groups' => ['order:read']],
)]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[Groups(['order:read'])]
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    public ?string $id;

    #[Groups(['order:read'])]
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $orderNumber;

    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => ['WAITING', 'COMPLETED', 'REFUNDED'],
                'example' => 'WAITING',
            ],
        ],
    )]
    #[Groups(['order:read'])]
    #[ORM\Column(type: 'string', enumType: Status::class)]
    private ?Status $status;

    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => ['WAITING', 'COMPLETED', 'REFUNDED'],
                'example' => 'WAITING',
            ],
        ],
    )]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Status::class, 'values'])]
    #[Groups(['order:write'])]
    #[SerializedName('status')]
    private ?string $plainStatus = null;

    #[ApiProperty(
        readableLink: false,
        writableLink: false,
        security: 'is_granted("ROLE_ADMIN")',
    )]
    #[Groups(['order:read', 'order:write'])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[Timestampable(on: 'create')]
    #[Groups(['order:read'])]
    #[ORM\Column(type: 'datetime')]
    protected ?DateTime $createdAt;

    #[Timestampable(on: 'update')]
    #[Groups(['order:read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $updatedAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(string|Status $status): void
    {
        $this->status = $status instanceof Status ? $status : Status::from($status);
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPlainStatus(): ?string
    {
        return $this->plainStatus;
    }

    public function setPlainStatus(string $plainStatus): void
    {
        $this->plainStatus = $plainStatus;
    }

    public function erasePlainStatus(): void
    {
        $this->plainStatus = null;
    }
}
