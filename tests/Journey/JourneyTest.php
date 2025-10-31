<?php
namespace App\Tests\Journey;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\AddressEntity;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\UserInformation;
use App\Entity\UserTokens;

/**
 * @group smoke
 */
class JourneyTest extends WebTestCase
{
public function testAdminJourneyTest()
{
    $client = static::createClient();
    $uniqueEmail = 'admin_'.uniqid().'@example.com';
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $client->request(
        'POST', 
        '/public/user',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'email' => $uniqueEmail,
            'password' => 'phpsymfonydocker12',
            'password_confirm' => 'phpsymfonydocker12'
        ])
    );
    $this->assertSame(201, $client->getResponse()->getStatusCode(), 'Admin Register Failed');

    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $uniqueEmail]);
    $userToken = $entityManager->getRepository(UserTokens::class)->findOneBy(['user' => $user]);
    
    $verificationToken = $userToken->getVerifyToken();

    $client->request('PATCH', "/public/user/{$verificationToken}");
    $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Admin Verification Failed');

    $client->request(
        'POST',
        '/login', 
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'username' => $uniqueEmail,
            'password' => 'phpsymfonydocker12'
        ])
    );
    $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Admin Login Failed');

    $client->request(
        'PATCH',
        '/auth/admin',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'password' => 'passwordUmDenUserZumAdminZuMachen'
        ])
    );
    $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Admin Promotion Failed');

    $entityManager->clear();
    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $uniqueEmail]);
    $client->loginUser($user);

    $client->request(
        'POST',
        '/admin/product',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'name' => 'CI Test Product '.uniqid(),
            'description' => 'Product created in CI test',
            'price' => 99.99,
            'stock' => 10,
            'category' => 'electronics',
            'image' => '/uploads/test-product.jpg'
        ])
    );
    $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Product Creation Failed');
    
    $productResponse = json_decode($client->getResponse()->getContent(), true);
    $productId = $productResponse['productId'];

    $client->request(
        'PATCH',
        "/admin/product/{$productId}",
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'price' => 89.99,
            'stock' => 15
        ])
    );
    $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Product Update Failed');

}

    public function testCustomerJourneyTest()
    {
        $client = static::createClient();
        $uniqueEmail = 'customer_'.uniqid().'@example.com';

        $client->request(
            'POST', 
            '/public/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'password' => 'phpsymfonydocker12',
                'password_confirm' => 'phpsymfonydocker12'
            ])
        );
        $this->assertSame(201, $client->getResponse()->getStatusCode(), 'Customer Register Failed');

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userToken = $entityManager->getRepository(UserTokens::class)
            ->findOneBy(['user' => $entityManager->getRepository(User::class)->findOneBy(['email' => $uniqueEmail])]);
        
        $verificationToken = $userToken->getVerifyToken();

        $client->request('PATCH', "/public/user/{$verificationToken}");
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Customer Verification Failed');

        $client->request(
            'POST',
            '/login', 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $uniqueEmail,
                'password' => 'phpsymfonydocker12'
            ])
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Customer Login Failed');

        $client->request('GET', '/public/product/dashboard');
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Produktliste fehlgeschlagen');
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $firstProduct = $data['products'][0] ?? null;
        $this->assertNotNull($firstProduct, 'Keine Produkte im Dashboard gefunden');
        
        $productId = $firstProduct['productId'];

        $client->request(
            'POST',
            '/auth/cart',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'productId' => $productId,
                'quantity' => 1
            ])
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Warenkorb hinzufügen fehlgeschlagen');

        $client->request(
            'POST',
            '/auth/deliveryAddress',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstname' => 'Journey',
                'lastname' => 'Test',
                'streetname' => 'Teststraße 123',
                'city' => 'Berlin',
                'postalCode' => '10115',
                'country' => 'Germany'
            ])
        );
        $this->assertSame(201, $client->getResponse()->getStatusCode(), 'Address erstellen fehlgeschlagen');

        $client->request(
            'POST',
            '/auth/paymentMethod',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'type' => 'credit_card',
                'provider' => 'stripe',
                'paymentMethodId' => 'pm_123_321',
                'brand' => 'visa', 
                'last4' => '4242',
                'isDefault' => true
            ])
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Payment Method erstellen fehlgeschlagen');

        $client->request('GET', '/auth/checkout-data');
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Checkout data holen fehlgeschlagen');
        
        $checkoutData = json_decode($client->getResponse()->getContent(), true);
        $addressId = $checkoutData['addresses'][0]['id'];
        $paymentId = $checkoutData['payments'][0]['id'];

        $client->request(
            'POST',
            '/auth/checkout-data',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'billingAddress' => $addressId,
                'shippingAddress' => $addressId, 
                'paymentMethod' => $paymentId
            ])
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Order erstellen fehlgeschlagen');
        
        $orderResponse = json_decode($client->getResponse()->getContent(), true);
        $orderId = $orderResponse['orderId'];
    }
}