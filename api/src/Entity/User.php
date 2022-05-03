<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Enum\Role;
use App\Enum\Sex;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        'get' => ['security' => 'is_granted("ROLE_ADMIN")'],
        'post',
    ],
    itemOperations: [
        'get' => ['security' => 'is_granted("ROLE_ADMIN") or object == user'],
        'put' => ['security' => 'is_granted("ROLE_ADMIN") or object == user'],
        'delete' => ['security' => 'is_granted("ROLE_ADMIN") or object == user'],
    ],
    denormalizationContext: ['groups' => ['user:write']],
    normalizationContext: ['groups' => ['user:read']],
)]
#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    public ?string $id;

    #[Assert\NotBlank]
    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column]
    private ?string $name;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email;

    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => Sex::class,
                'example' => 'MALE',
            ],
        ],
    )]
    #[Groups(['user:read'])]
    #[ORM\Column(type: 'string', enumType: Sex::class)]
    private ?Sex $sex;

    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => ['MALE', 'FEMALE', 'OTHER'],
                'example' => 'MALE',
            ],
        ],
    )]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Sex::class, 'values'])]
    #[Groups(['user:write'])]
    #[SerializedName('sex')]
    private ?string $plainSex = null;

    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => ['ROLE_USER', 'ROLE_ADMIN'],
                'example' => 'ROLE_USER',
            ],
        ],
        security: 'is_granted("ROLE_ADMIN")',
    )]
    #[Assert\Choice(callback: [Role::class, 'values'])]
    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $roles;

    #[ORM\Column(type: 'string')]
    private ?string $password;

    #[Assert\NotBlank]
    #[Groups(['user:write'])]
    #[SerializedName('password')]
    private ?string $plainPassword;

    #[ApiSubresource]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Order::class)]
    private Collection $orders;

    #[Groups(['user:read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ordersCount;

    #[Timestampable(on: 'update')]
    #[Groups(['user:read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $updatedAt;

    #[Timestampable(on: 'create')]
    #[Groups(['user:read'])]
    #[ORM\Column(type: 'datetime')]
    protected ?DateTime $createdAt;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->ordersCount = 0;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSex(): Sex
    {
        return $this->sex;
    }

    public function setSex(string|Sex $sex): void
    {
        $this->sex = $sex instanceof Sex ? $sex : Sex::tryFrom($sex);
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        $roles[] = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(string $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOrdersCount(): int
    {
        return $this->ordersCount;
    }

    public function setOrdersCount(int $ordersCount): self
    {
        $this->ordersCount = $ordersCount;

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

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setOwner($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getOwner() === $this) {
                $order->setOwner(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPlainSex(): ?string
    {
        return $this->plainSex;
    }

    public function setPlainSex(string $plainSex): void
    {
        $this->plainSex = $plainSex;
    }

    public function erasePlainSex(): void
    {
        $this->plainSex = null;
    }
}
