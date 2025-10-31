<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Uid\Uuid;

final class ProductController extends AbstractController
{
    function generateSlug(string $title): string {

        $slug = mb_strtolower($title, 'UTF-8');

        $search  = ['ä', 'ö', 'ü', 'ß'];
        $replace = ['ae', 'oe', 'ue', 'ss'];
        $slug = str_replace($search, $replace, $slug);

        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);

        $slug = trim($slug, '-');

        return $slug;
    }

    #[Route('/admin/product', name: 'create_products', methods: ['POST'])]
    #[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["name" => "Product XYZ", "description" => "Description", "price" => 99.99, "stock" => 10, "category" => "electronics"]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Product successfully created", "productId" => "uuid123", "productSlug" => "product-xyz"]
    )
)]
    public function createProduct(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setStock($data['stock']);
        $product->setCategory($data['category']);
        $product->setImage($data['image']);

        $now = new \DateTimeImmutable('now');
        $product->setCreatedAt($now);
        $product->setUpdatedAt($now);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], 400);
        }

        $product->setSlug($this->generateSlug($data['name']));

    $generatedProductId = Uuid::v4()->toRfc4122();

        $product->setProductId($generatedProductId);

        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product successfully created',
            'productId' => $product->getProductId(),
            'productSlug' => $product->getSlug(),
        ]);
    }

    #[Route('/admin/product/{productId}', name: 'edit_product', methods: ['PATCH'])]
    #[OA\Parameter(name: 'productId', in: 'path', description: 'ID', example: 'uuid123')]
#[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["name" => "Updated Product", "price" => 89.99, "stock" => 5]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Product successfully updated", "productId" => "uuid123"]
    )
)]
    public function editProduct(
        string $productId,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $product = $em->getRepository(Product::class)->findOneBy(['productId' => $productId]);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
            $product->setSlug($this->generateSlug($data['name']));
        }

        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        if (isset($data['stock'])) {
            $product->setStock($data['stock']);
        }

        if (isset($data['isActive'])) {
            $product->setIsActive((bool) $data['isActive']);
        }

        if (isset($data['discountPrice'])) {
            $product->setDiscountPrice($data['discountPrice']);
        }

        if(isset($data['image'])) {
            $product->setImage($data['image']);
        }

        $now = new \DateTimeImmutable('now');
        $product->setUpdatedAt($now);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], 400);
        }

        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product successfully updated',
            'productId' => $product->getProductId(),
            'updated-at' => $product->getUpdatedAt(),
        ]);
    }

    #[Route('/admin/product/{productId}', name: 'delete_product', methods: ['DELETE'])]
    #[OA\Parameter(name: 'productId', in: 'path', description: 'ID', example: 'uuid123')]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Product successfully deleted", "productId" => "uuid123"]
    )
)]
    public function deleteProduct(
        string $productId,
        EntityManagerInterface $em,
        Request $request
    ): JsonResponse {

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $em->remove($product);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product successfully deleted',
            'productId' => $product->getProductId(),
        ], 200);
    }

    #[Route('/public/product/dashboard', name: 'api_products_dashboard', methods: ['GET'])]
    #[OA\Response(
    response: 200,
    description: "OK", 
    content: new OA\JsonContent(
        example: ["success" => true, "products" => [["productId" => "uuid123", "name" => "Product XYZ", "price" => 99.99]]]
    )
)]
    public function dashboard(
        EntityManagerInterface $em,
    ): JsonResponse {

        $newestProducts = $em->getRepository(Product::class)
            ->createQueryBuilder('Np')
            ->where('Np.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('Np.created_at', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        $discountedProducts = $em->getRepository(Product::class)
            ->createQueryBuilder('dp')
            ->where('dp.isActive = :active')
            ->andWhere('dp.price > dp.discountPrice')
            ->setParameter('active', true)
            ->orderBy('dp.price - dp.discountPrice', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        $products = array_unique(array_merge($newestProducts, $discountedProducts), SORT_REGULAR);

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'productId' => $product->getProductId(),
                'slug' => $product->getSlug(),
                'category' => $product->getCategory(),
                'name' => $product->getName(),
                'price' => $product->getFinalPrice(),
                'image' => $product->getImage(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'products' => $data,
        ]);
    }

    #[Route('/public/product/{slug?}/{productId}', name: 'public_products_detail', methods: ['GET'])]
    #[OA\Parameter(name: 'productId', in: 'path', description: 'ID', example: 'uuid123')]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "product" => ["productId" => "uuid123", "name" => "Product XYZ", "price" => 99.99, "stock" => 10]]
    )
)]
    public function details(
        string $productId,
        EntityManagerInterface $em,
    ): JsonResponse {

        $product = $em->getRepository(Product::class)->findOneBy([
            'productId' => $productId,
        ]);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        if(!$product->isActive()) {
            return new JsonResponse(['error' => 'Product not active'], 404);
        }

        $data = [
            'productId' => $product->getProductId(),
            'name' => $product->getName(),
            'price' => $product->getFinalPrice(),
            'stock' => $product->getStock(),
            'slug' => $product->getSlug(),
            'category' => $product->getCategory(),
            'description' => $product->getDescription(),
            'image' => $product->getImage(),
        ];

        return new JsonResponse([
            'success' => true,
            'primary Identifier' => $product->getId(),
            'product' => $data,
        ]);
    }

    #[Route('/public/product/search', name: 'public_products_search', methods: ['GET'])]
    #[OA\Parameter(name: 'q', in: 'query', description: 'Search', example: 'electronics')]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "products" => [["productId" => "uuid123", "name" => "Product XYZ", "price" => 99.99]]]
    )
)]
    public function search(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $query = $request->query->get('q', '');
        if (empty($query)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No search query provided',
            ], 400);
        }

        $limit = $request->query->getInt('limit', 20);

        $qb = $em->getRepository(Product::class)->createQueryBuilder('p');
        $qb->where('LOWER(p.slug) LIKE :term OR LOWER(p.category) LIKE :term')
            ->setParameter('term', '%' . strtolower($query) . '%')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit);

        $products = $qb->getQuery()->getResult();

        $data = array_map(fn(Product $p) => [
            'id' => $p->getId(),
            'productId' => $p->getProductId(),
            'slug' => $p->getSlug(),
            'name' => $p->getName(),
            'price' => $p->getFinalPrice(),
            'stock' => $p->getStock(),
            'image' => $p->getImage(),
        ], $products);

        return new JsonResponse([
            'success' => true,
            'products' => $data,
        ]);
    }

    #[Route('/admin/status/{productId}', name: 'status_product', methods: ['PATCH'])]
    #[OA\Parameter(name: 'productId', in: 'path', description: 'ID', example: 'uuid123')]
#[OA\RequestBody(
    content: new OA\JsonContent(
        example: ["stock" => 15, "isActive" => true]
    )
)]
#[OA\Response(
    response: 200,
    description: "OK",
    content: new OA\JsonContent(
        example: ["success" => true, "message" => "Product status successfully updated", "productId" => "uuid123", "stock" => 15, "isActive" => true]
    )
)]
    public function status(
        string $productId,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], 401);
        }

        $product = $em->getRepository(Product::class)->findOneBy(['productId' => $productId]);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

    
        if (isset($data['stock'])) {
            $newStock = (int) $data['stock'];
            $product->setStock($newStock);


            if ($newStock <= 0) {
                $product->setIsActive(false);
            }
        }


        if (isset($data['isActive'])) {
            $isActive = (bool) $data['isActive'];

    
            if ($isActive && $product->getStock() < 1) {
                return new JsonResponse([
                    'error' => 'Product cannot be activated because stock is 0.'
                ], 400);
            }

            $product->setIsActive($isActive);
        }


        $product->setUpdatedAt(new \DateTimeImmutable('now'));

        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Product status successfully updated',
            'productId' => $product->getProductId(),
            'stock' => $product->getStock(),
            'isActive' => $product->isActive(),
        ]);
    }
}
