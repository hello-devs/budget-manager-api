<?php

namespace Tests\units\Controller;

use App\Controller\UserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class UserControllerTest extends TestCase
{
    public function test_that_request_user_info_with_null_user_return_403(): void
    {
        //  Given
        $serializer = $this->createMock(SerializerInterface::class);
        $security = $this->createMock(Security::class);
        $security->method("getUser")->willReturn(null);

        $controller = new UserController();


        //  When
        $result = $controller->getCurrentUserInfo($security,$serializer);

        //  Then
        $this->assertEquals(403, $result->getStatusCode());
    }
}