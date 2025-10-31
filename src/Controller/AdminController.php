<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTime;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class AdminController extends AbstractController
{
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    #[Route('/admin/time', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the current server timezone name'
    )]
    public function timezone(): Response
    {
        $now = new DateTime();

        return new Response($now->getTimezone()->getName());
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'check the DB connection'
    )]
    public function __invoke(): JsonResponse
    {
        try {
            $result = $this->conn->executeQuery('SELECT 1')->fetchOne();
            if ($result === false) {
                throw new \RuntimeException('DB returned no result');
            }
            return new JsonResponse(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'error'], 503);
        }
    }

    #[Route('/auth/admin', name: 'auth_admin_set', methods: ['PATCH'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: ["password" => "meinTestPasswort"]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "OK",
        content: new OA\JsonContent(
            example: ["message" => "User Role set to Admin"]
        )
    )]
    public function setAdmin(
        Request $request, 
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not Authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $inputPassword = $data['password'] ?? null;

        $adminPassword = 'passwordUmDenUserZumAdminZuMachen';

        if (!$inputPassword || $inputPassword !== $adminPassword) {
            return new JsonResponse(['error' => 'Wrong Credentials'], 403);
        }

        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();

        return new JsonResponse(['message' => 'User Role set to Admin']);
    }
}