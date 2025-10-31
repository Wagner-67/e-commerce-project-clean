<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["username" => "user@example.com", "password" => "pass123"]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["user" => ["id" => 1, "email" => "user@example.com"]]
    )
)]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if(empty($data['username']) || empty($data['password'])) {
            return $this->json(['message' => 'Please provide your Email and password'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['username']]);

        if(!$user) {
            return new JsonResponse(['error'=>'Account does not exist'], 400);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error'=>'Invalid credentials'], 400);
        }

        if(!$user->isVerified()) {

            $request->getSession()->invalidate();

            $verificationUrl = $urlGenerator->generate(
                'public_verify_account',
                ['token' => $user->getUserToken()->getVerifyToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Account Verification')
                ->html("<p>Please verify your account by clicking the link below:</p>
            <p><a href=\"$verificationUrl\">Verify Account</a></p>
            <p>If you did not create this account, ignore this email.</p>");

            $mailer->send($email);

            return new JsonResponse([
                'error' => 'Your account is not Verified',
                'message' => 'We send you an Email to verify your account.',
            ]);
        }

        $now = new \DateTimeImmutable('now');
        $user->setLastLogin($now);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'isVerified' => $user->isVerified(),
            ]
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {

        throw new \LogicException('Logout wird von Symfony Security gehandhabt.');

    }
}

