<?php

namespace App\Entity;

class User
{

    private string $username;


    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
    public function getUsername(): ?string
    {
        return $this->username;
    }


}
