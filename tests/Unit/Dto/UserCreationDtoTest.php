<?php

namespace Tests\Unit\Dto;

use App\Dto\UserCreationDto;
use PHPUnit\Framework\TestCase;

class UserCreationDtoTest extends TestCase
{
    public function test_we_can_instantiate_user_creation_dto_object(): void
    {
        $userCreationDto = new UserCreationDto();

        $this->assertInstanceOf(UserCreationDto::class, $userCreationDto);
    }
}
