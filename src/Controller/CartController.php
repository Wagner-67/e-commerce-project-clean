<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class CartController extends AbstractController
{
    #[Route('/auth/cart', name: 'auth_cart_add_product', methods: ['POST'])]
     #[OA\RequestBody(
        description: "Add product to cart",
        content: new OA\JsonContent(
            example: ["productId" => "xyz123", "quantity" => 1]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Product added successfully",
        content: new OA\JsonContent(
            example: ["success" => true, "message" => "Product added to cart", "total" => 99.99, "product price" => 99.99, "product id" => "xyz123", "quantity" => 1]
        )
    )]
    public function add(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'User not Authorized'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        $quantity = (int) ($data['quantity'] ?? 1);

        if (!$productId) {
            return new JsonResponse(['message' => 'Product ID required'], 400);
        }

        $product = $em->getRepository(Product::class)->findOneBy(['productId' => $productId]);

        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], 404);
        }

        if ($product->getStock() < 1) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product is out of stock and cannot be added to the cart.'
            ], 400);
        }

        if ($quantity > $product->getStock()) {
            return new JsonResponse([
                'success' => false,
                'message' => sprintf(
                    'Only %d units of "%s" are available in stock.',
                    $product->getStock(),
                    $product->getName()
                )
            ], 400);
        }

        if ($user->getCart() === null) {
            $cart = new Cart();
            $cart->setUser($user);
            $user->setCart($cart);
            $cart->setTotalPrice('0.0');
            $cart->setUpdatedAt(new \DateTimeImmutable('now'));
            $em->persist($cart);
            $em->flush();
        }

        $userCart = $user->getCart();

        $existingItem = $em->getRepository(CartItem::class)->findOneBy([
            'cart' => $userCart,
            'productId' => $productId
        ]);

        if ($existingItem) {
            $currentQuantity = $existingItem->getQuantity();
            $newQuantity = $currentQuantity + $quantity;

            if ($newQuantity > $product->getStock()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => sprintf(
                        'Only %d units of "%s" are available in stock.',
                        $product->getStock(),
                        $product->getName()
                    )
                ], 400);
            }

            $existingItem->setQuantity($newQuantity);
            $existingItem->setProductPrice($newQuantity * $product->getFinalPrice());

        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($userCart);
            $cartItem->setQuantity($quantity);
            $cartItem->setProductPrice($product->getFinalPrice() * $quantity);
            $cartItem->setProductId($productId);
            $cartItem->setAddedAt(new \DateTimeImmutable());
            $em->persist($cartItem);
        }

        $em->flush();

        $cartItems = $em->getRepository(CartItem::class)->findBy(['cart' => $userCart]);
        $total = array_sum(array_map(fn($item) => $item->getProductPrice(), $cartItems));

        $userCart->setTotalPrice(number_format($total, 2, '.', ''));
        $userCart->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($userCart);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'total' => $total,
            'product price' => $product->getFinalPrice(),
            'product id' => $productId,
            'quantity' => $quantity
        ]);
    }

    #[Route('/auth/cart/list', name: 'auth_list_cart', methods: ['GET'])]
     #[OA\Response(
        response: 200,
        description: "Cart items retrieved",
        content: new OA\JsonContent(
            example: ["success" => true, "Items" => [["id" => 1, "productId" => "xyz123", "quantity" => 1, "productPrice" => 99.99]]]
        )
    )]
    public function list(
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $this->getUser();

        if(!$user instanceof User){
            return new JsonRepsonse(['message'=>'User not Authorized'], 403);
        }

        $cartItems = $user->getCart()->getCartItems();

        $data= [];

        foreach ($cartItems as $item) {
            $data[] = [
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
            'productPrice' => $item->getProductPrice(),
            'name' => $product?->getName(),
            'image' => $product?->getImage(),
            'category' => $product?->getCategory(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'Items' => $data,
        ]);
    }

    #[Route('/auth/cart/{productId}', name: 'auth_cart_delete_product', methods: ['DELETE'])]
     #[OA\Parameter(name: 'productId', in: 'path', description: 'Product ID', example: 'xyz123')]
    #[OA\Response(
        response: 200,
        description: "Product removed from cart",
        content: new OA\JsonContent(
            example: ["success" => true, "message" => "Product deleted from cart", "total" => 0.00, "productId" => "xyz123"]
        )
    )]
    public function delete(
        string $productId,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $user = $this->getUser();

        if(!$user instanceof User) {
            return new JsonResponse(['message'=>'User not Authorized'], 403);
        }

        $product = $em->getRepository(Product::class)->findOneBy(['productId'=>$productId]);

        if(!$product) {
            return new JsonResponse(['message'=>'Product not found'], 404);
        }

        $userCart = $user->getCart();

        if(!$userCart) {
            return new JsonResponse(['message'=>'Cart not found'], 404);
        }

        $existingItem = $em->getRepository(CartItem::class)->findOneBy([
            'cart' => $userCart,
            'productId' => $productId
        ]);

        if(!$existingItem) {
            return new JsonResponse(['message'=>'Product not found in cart'], 404);
        }

        if($existingItem->getQuantity() > 1) {

            $currentQuantity = $existingItem->getQuantity() - 1;
            $existingItem->setQuantity($currentQuantity);

            $newPrice = $currentQuantity * $product->getFinalPrice();
            $existingItem->setProductPrice($newPrice);

            $em->persist($existingItem);

        } else {

            $em->remove($existingItem);
        }

        $em->flush();

        $cartItems = $em->getRepository(CartItem::class)->findBy(['cart' => $userCart]);

        $total = 0;
        foreach($cartItems as $item) {
            $total += $item->getProductPrice();
        }

        $userCart->setTotalPrice(number_format($total, 2, '.', ''));
        $userCart->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($userCart);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product deleted from cart',
            'total' => $total,
            'productId' => $productId,
        ]);
    }
}
