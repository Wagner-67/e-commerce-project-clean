<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class StockCounterListener
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[AsEventListener]
    public function onResponseEvent(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName !== 'auth_checkout_pay') {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $order = $this->em->getRepository(Order::class)->findOneBy(
            ['user' => $user], 
            ['id' => 'DESC']
        );

        if (!$order || !$order->getCart()) {
            return;
        }

        $cart = $order->getCart();
        $cartItems = $cart->getCartItems();

        foreach ($cartItems as $cartItem) {
            $product = $this->em->getRepository(Product::class)->findOneBy([
                'productId' => $cartItem->getProductId()
            ]);

            if (!$product) {
                throw new BadRequestHttpException(sprintf(
                    'Product with ID %s not found.', 
                    $cartItem->getProductId()
                ));
            }

            $newStock = $product->getStock() - $cartItem->getQuantity();

            if ($newStock < 0) {
                throw new BadRequestHttpException(sprintf(
                    'Product "%s" is not available. Only %d in stock, but %d requested.',
                    $product->getName(),
                    $product->getStock(),
                    $cartItem->getQuantity()
                ));
            }

            $product->setStock($newStock);

            if ($newStock === 0) {
                $product->setIsActive(false);
            }

            $this->em->persist($product);
        }

        $this->em->flush();
    }
}