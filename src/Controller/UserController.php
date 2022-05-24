<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route(path: "/api/users/me", name: "get_current_user_info", methods: ['GET'])]
    public function getCurrentUserInfo(Security $security, SerializerInterface $serializer): JsonResponse
    {
        $user = $security->getUser();

        if ($user == null) {
            return new JsonResponse(status: 403);
        }

        return new JsonResponse(data: $serializer->serialize($user, "json"), json: true);
    }
}
