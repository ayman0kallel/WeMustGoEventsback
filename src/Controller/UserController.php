<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends AbstractController
{
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager) {
    $this->entityManager = $entityManager;
    $this->jwtManager = $jwtManager;
    }

    #[Route('/loggedUser', name: 'loggedUser', methods: ['GET'])]
    public function loggedUser(Request $request): Response
    {
        $token = $request->headers->get('authorization');
        $token = explode(' ',(string) $token)[1];
        $obj = $this->jwtManager->parse($token);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $obj['username']]);
        $normalizer = new ObjectNormalizer(null);

        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
        $json = $serializer->serialize($user, 'json');

        // Return the user data as JSON response
        return new Response($json);
    }
    
}
