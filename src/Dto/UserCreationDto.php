<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
class UserCreationDto
{
    #[Groups(["write"])]
    #[Assert\Email()]
    public string $email;

    #[Groups(["write"])]
    #[SerializedName("password")]
    #[Assert\Length(min:6)]
    public string $plainPassword;

    /**
     * @var string[]
     */
    #[Groups(["write"])]
    public array $roles = [];
}
