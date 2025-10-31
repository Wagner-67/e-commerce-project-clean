<?php

namespace App\Controller;

use App\Entity\AddressEntity;
use App\Entity\Payment;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserTokens;
use App\Enum\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserController extends AbstractController
{
    #[Route('/public/user', name: 'user_register', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["email" => "user@example.com", "password" => "securepassword123", "password_confirm" => "securepassword123"]
    )
)]
#[OA\Response(
    response: 201,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Your account has been created. You achieved an Verification email"]
    )
)]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if(empty($data['email']) || empty($data['password']) || empty($data['password_confirm'])){
            return new JsonResponse(['error'=>'All fields are required.'], 400);
        }

        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error'=>'Invalid email format.'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if($user){
           return new JsonResponse(['error'=>'User already exists.'], 400);
        }

        if($data['password'] !== $data['password_confirm']){
            return new JsonResponse(['error'=>'Password and confirmation do not match.'], 400);
        }

        if(strlen($data['password']) < 12 || strlen($data['password']) > 64){
            return new JsonResponse(['error'=>'Password length must be between 12 and 64 characters.'], 400);
        }

        $violations = $validator->validate(
            $data['password'],
            new \App\Validator\PwnedPassword()
        );

        if (count($violations) > 0) {
            return new JsonResponse(['error' => $violations[0]->getMessage()], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $now = new \DateTimeImmutable('now');
        $user->setAccCreated($now);

        $generatedToken = Uuid::v4()->toRfc4122();

        $userToken = new UserTokens();
        $userToken->setUser($user);
        $userToken->setVerifyToken($generatedToken);
        $userToken->setVerifyTokenAt($now);

        $user->setUserToken($userToken);

        $em->persist($user);
        $em->persist($userToken);
        $em->flush();

        $verificationUrl = $urlGenerator->generate(
            'public_verify_account',
            ['token' => $generatedToken],
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
            'success' => true,
            'message' => "Your account has been created. You achieved an Verification email"
        ], 201);
    }
    #[Route('/public/user/{token}', name: 'public_verify_account', methods: ['PATCH', 'GET'])]
    #[OA\Parameter(name: 'token', in: 'path', description: 'Token', example: 'uuid123')]
#[OA\Response(
    response: 200,
    description: "OK", 
    content: new OA\JsonContent(
        example: ["message" => "Your account has been successfully verified."]
    )
)]
#[OA\Parameter(name: 'token', in: 'path', description: 'Token', example: 'uuid123')]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["message" => "Your account has been successfully verified."]
    )
)]
    public function verifyAccount(
        string $token,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        if(!$token){
            return new JsonResponse(['error'=>'Token is Missing']);
        }

        $userToken = $em->getRepository(UserTokens::class)->findOneBy(['verifyToken' => $token]);

        if(!$userToken){
            return new JsonResponse(['error'=>'Invalid token or user']);
        }

        $userToken->getUser()->setVerified(true);
        $userToken->setVerifyTokenAt(null);
        $userToken->setVerifyToken(null);

        $em->flush();

        return new JsonResponse(['message' => 'Your account has been successfully verified.']);
    }
}
