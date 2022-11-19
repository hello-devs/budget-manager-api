<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\UserCreationDto;
use App\Repository\UserRepository;
use App\State\UserCreationProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted("ROLE_ADMIN")', securityMessage: 'Only admins can get users lists.'),
        new Put(security: 'object == user', securityMessage: 'Denied permission'),
        new GetCollection(security: 'is_granted("ROLE_ADMIN")', securityMessage: 'Only admins can get users lists.'),
        new Post(
            security: 'is_granted("ROLE_CLIENT")',
            securityMessage: 'Only authorized role can create user',
            input: UserCreationDto::class,
            processor: UserCreationProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)
]
#[ORM\Table(name: "`user`"), ]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Assert\NotBlank]
    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Groups(["user-info", "read", "write"])]
    private string $email;
    #[SerializedName("password")]
    #[Groups(["write"])]
    private ?string $plainPassword = null;
    #[Assert\NotBlank]
    #[ORM\Column(type: "string")]
    private string $password;
    /** @var array<string> $roles */
    #[ORM\Column(type: "json")]
    #[Groups(["write"])]
    private array $roles = [];
    /** @var Collection<int, Budget> */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Budget::class, orphanRemoval: true)]
    #[Groups(["user-info", "read", "write"])]
    private Collection $budget;
    /** @var Collection<int, Transaction> */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Groups(["read", "write"])]
    private Collection $transaction;

    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: "integer")]
        #[Groups(["user-info", "read"])]
        private ?int $id = null
    ) {
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
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
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
