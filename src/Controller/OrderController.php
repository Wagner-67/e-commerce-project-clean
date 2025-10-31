<?php

namespace App\Controller;

use App\Entity\AddressEntity;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Product;
use App\Enum\PaymentMethod;
use OpenApi\Attributes as OA;

final class OrderController extends AbstractController
{
    #[Route('/auth/checkout-data', name: 'auth_list_checkout-data', methods: ['GET'])]
    #[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["status" => "success", "addresses" => [["id" => 1, "firstname" => "Max"]], "payments" => [["id" => 1, "type" => "credit_card"]]]
    )
)]
    public function listOrder(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'user is not Authenticated'], 401);
        }

        $addresses = $em->getRepository(AddressEntity::class)->findBy(['user' => $user]);
        $addressData = array_map(fn(AddressEntity $address) => [
            'id' => $address->getId(),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'streetName' => $address->getStreetName(),
            'city' => $address->getCity(),
            'postalCode' => $address->getPostalCode(),
            'country' => $address->getCountry(),
        ], $addresses);

        $payments = $em->getRepository(Payment::class)->findBy(['user' => $user]);
        $paymentData = array_map(fn(Payment $payment) => [
            'id' => $payment->getId(),
            'type' => $payment->getType(),
            'provider' => $payment->getProvider(),
            'label' => $payment->getLabel(),
            'isDefault' => $payment->isDefault(),
        ], $payments);

        return new JsonResponse([
            'status' => 'success',
            'addresses' => $addressData,
            'payments' => $paymentData,
        ]);
    }

    #[Route('/auth/checkout-data', name: 'auth_set_checkout_data', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["billingAddress" => 1, "shippingAddress" => 1, "paymentMethod" => 1]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK", 
    content: new OA\JsonContent(
        example: ["status" => "success", "message" => "Order created successfully", "orderId" => 1]
    )
)]
    public function setCheckoutData(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'user is not Authenticated']);
        }


        if($user->getOrders() && count($user->getOrders()) > 0) {
            return new JsonResponse(['message'=>'There is already one Order as setup'], 400);
        }

        $data = json_decode($request->getContent(), true);

        $billingAddressId = $data['billingAddress'] ?? null;
        $shippingAddressId = $data['shippingAddress'] ?? null;
        $paymentMethodId = $data['paymentMethod'] ?? null;

        $billingAddress = $em->getRepository(AddressEntity::class)->find($billingAddressId);
        if (!$billingAddress || $billingAddress->getUser() !== $user) {
            return new JsonResponse(['message' => 'Invalid billing address'], 400);
        }

        $shippingAddress = $em->getRepository(AddressEntity::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            return new JsonResponse(['message' => 'Invalid shipping address'], 400);
        }

        $paymentMethod = $em->getRepository(Payment::class)->find($paymentMethodId);
        if (!$paymentMethod || $paymentMethod->getUser() !== $user) {
            return new JsonResponse(['message' => 'Invalid payment method'], 400);
        }

        $cart = $user->getCart();
        if (!$cart) {
            return new JsonResponse(['message' => 'No cart found for user'], 400);
        }

        if(!$cart->getCartItems() || count($cart->getCartItems()) === 0) {
            return new JsonResponse(['message'=>'No products in cart'], 400);
        }

        $order = new Order();
        $order->setUser($user);
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->setPaymentMethod($paymentMethod);
        $order->setCart($cart);
        $order->setTotalAmount($cart->getTotalPrice());
        $order->setStatus('setup');

        $em->persist($order);
        $em->flush();

        return new JsonResponse([
            'status' => 'success', 
            'message' => 'Order created successfully',
            'orderId' => $order->getId(),
            'totalAmount' => $cart->getTotalPrice(),
            'orderStatus' => $order->getStatus(),
                ]);
            }

    #[Route('/auth/deliveryAddress', name: 'auth_add_address', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["firstname" => "Max", "lastname" => "Mustermann", "streetname" => "Hauptstr. 1", "city" => "Berlin", "postalCode" => "10115", "country" => "Germany"]
    )
)]
#[OA\Response(
    response: 201,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "User information created successfully", "id" => 1]
    )
)]
    public function deliveryAddress(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $address = new AddressEntity();
        $address->setUser($user);
        $address->setFirstname($data['firstname'] ?? null);
        $address->setLastname($data['lastname'] ?? null);
        $address->setStreetName($data['streetname'] ?? null);
        $address->setCity($data['city'] ?? null);
        $address->setPostalCode($data['postalCode'] ?? null);
        $address->setCountry($data['country'] ?? null);

        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], 400);
        }

        $address->setCreatedAt(new \DateTimeImmutable('now'));

        $em->persist($address);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'User information created successfully.',
            'id' => $address->getId(),
        ], 201);
    }

    #[Route('/auth/deliveryAddress/{addressId}', name: 'auth_delete_address', methods: ['DELETE'])]
    #[OA\Parameter(name: 'addressId', in: 'path', description: 'ID', example: 1)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Address deleted successfully", "id" => 1]
    )
)]
    public function deleteAddress(
        Request $request,
        EntityManagerInterface $em,
        string $addressId
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $address = $em->getRepository(AddressEntity::class)->find($addressId);

        if(!$address) {
            return new JsonResponse(['error' => 'Address not found.'], 404);
        }

        if($address->getUser() !== $user) {
            return new JsonResponse(['error' => 'You are not allowed to access this address.'], 403);
        }

        $em->remove($address);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Address deleted successfully.',
            'id' => $address->getId(),
        ]);
    }

    #[Route('/auth/paymentMethod', name: 'auth_add_payment_method', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["type" => "credit_card", "provider" => "stripe", "brand" => "visa", "last4" => "4242"]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "id" => 1]
    )
)]
    public function paymentMethod(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['paymentMethodId']) || empty($data['type']) || empty($data['provider'])) {
            return new JsonResponse(['error' => 'Incomplete payment data.'], 400);
        }

        $payment = new Payment();
        $payment->setUser($user);
        $payment->setType(PaymentMethod::from($data['type']));
        $payment->setProvider($data['provider']);
        $payment->setProviderPaymentId($data['paymentMethodId']);

        if ($data['type'] === 'credit_card') {
            $payment->setLabel($data['brand'].' •••• '.$data['last4']);
        } elseif ($data['type'] === 'paypal') {
            $payment->setLabel('PayPal '.$data['payerName']);
        }

        $payment->setIsDefault($data['isDefault'] ?? false);

        $em->persist($payment);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $payment->getId()]);
    }

    #[Route('/auth/paymentMethod/{paymentId}', name: 'auth_delete_payment_method', methods: ['DELETE'])]
    #[OA\Parameter(name: 'paymentId', in: 'path', description: 'ID', example: 1)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Payment deleted successfully", "id" => 1]
    )
)]
    public function deletePaymentMethod(
        Request $request,
        EntityManagerInterface $em,
        string $paymentId
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $payment = $em->getRepository(Payment::class)->find($paymentId);

        if(!$payment) {
            return new JsonResponse(['error' => 'Payment not found.'], 404);
        }

        if($payment->getUser() !== $user) {
            return new Jsonresponse(['error' => 'You are not allowed to access this payment.'], 403);
        }

        $em->remove($payment);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Payment deleted successfully.',
            'id' => $payment->getId(),
        ]);

    }
    #[Route('/auth/checkout-pay', name: 'auth_checkout_pay', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["orderId" => 1]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["status" => "success", "message" => "Payment successful", "orderId" => 1]
    )
)]
    public function checkoutPay(
    Request $request,
    EntityManagerInterface $em
): JsonResponse {

    $user = $this->getUser();
    if (!$user instanceof User) {
        return new JsonResponse(['message' => 'User is not authenticated'], 401);
    }

    $data = json_decode($request->getContent(), true);
    $orderId = $data['orderId'] ?? null;

    if (!$orderId) {
        return new JsonResponse(['message' => 'Order ID is required'], 400);
    }

    $order = $em->getRepository(Order::class)->find($orderId);

    if (!$order || $order->getUser() !== $user) {
        return new JsonResponse(['message' => 'Order not found or access denied'], 404);
    }

    if ($order->getStatus() !== 'setup') {
        return new JsonResponse(['message' => 'Order is already paid or cannot be processed'], 400);
    }

    $order->setStatus('paid');

    $em->flush();

    return new JsonResponse([
        'status' => 'success',
        'message' => 'Payment successful',
        'orderId' => $order->getId(),
        'orderStatus' => $order->getStatus()
    ]);
}

}