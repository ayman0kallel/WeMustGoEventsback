<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User; 
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class SecurityController extends AbstractController
{

    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;

    // Inject JWTTokenManagerInterface into your controller
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EntityManagerInterface $entityManager
    ){
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function signup(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        // Set default role or customize based on your needs
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }

    #[Route('/signin', name: 'signin', methods: ['POST'])]
    public function signin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return $this->json(['message' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        // Prepare user data to return in the response
        $userData = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];

        // Return the token and user data in the response
        return $this->json(['token' => $token, 'user' => $userData]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
        public function logout(): Response
        {
            return $this->json(['message' => 'Logged out successfully'], Response::HTTP_OK);
        }

    
}
