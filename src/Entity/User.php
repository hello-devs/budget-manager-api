<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\Table(name: "`user`"),
    ApiResource(
        collectionOperations: [
            "get" => [
                "security" => "is_granted('ROLE_ADMIN')",
                "security_message" => "Only admins can get users lists.",
            ],
            "post" => [
                "security" => "is_granted('ROLE_CLIENT')",
                "security_message" => "Only admins can create user",
            ]
        ],
        itemOperations: [
            "get" => [
                "security" => "is_granted('ROLE_CLIENT')",
                "security_message" => "Only admins can get users lists.",
            ],
            "put" => [
                "security" => "object == user",
                "security_message" => "Denied permission",
            ]
        ],
        denormalizationContext: ['groups' => ['write']],
        normalizationContext: ['groups' => ['read']]
    )
]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(["user-info", "read"])]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Groups(["user-info", "read", "write"])]
    private string $email;

    #[Assert\NotBlank]
    #[SerializedName("password")]
    #[Groups(["write"])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: "string")]
    private string $password;

    /** @var array<string> $roles */
    #[ORM\Column(type: "json")]
    #[Groups(["write"])]
    private array $roles = [];

    /** @var Collection<int, Budget> */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Budget::class, orphanRemoval: true)]
    #[Groups(["user-info","read", "write"])]
    private Collection $budget;

    /** @var Collection<int, Transaction> */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Groups(["read", "write"])]
    private Collection $transaction;

    public function __construct()
    {
        $this->budget = new ArrayCollection();
        $this->transaction = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return string the hashed password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $this->roles[] = 'ROLE_USER';
        $this->roles = array_unique($this->roles);

        return $this->roles;
    }

    /**
     * @param array<string> $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudget(): Collection
    {
        return $this->budget;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransaction(): Collection
    {
        return $this->transaction;
    }
}
