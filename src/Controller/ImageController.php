<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use OpenApi\Attributes as OA;

final class ImageController extends AbstractController
{
    #[Route('/public/upload', name: 'public_upload_image', methods: ['POST'])]
    #[OA\RequestBody(
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "file", type: "string", format: "binary")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "OK", 
        content: new OA\JsonContent(
            example: ["success" => true, "url" => "/uploads/img_67890abc123.png"]
        )
    )]
    public function uploadFile(
    Request $request
    ): JsonResponse {

        $file = $request->files->get('file'); 

        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return $this->json(['error' => 'Invalid file type'], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['error' => 'File too large'], 400);
        }

        $safeName = uniqid('img_', true) . '.' . $file->guessExtension();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $file->move($uploadDir, $safeName);

        return $this->json([
            'success' => true,
            'url' => '/uploads/' . $safeName,
        ]);
    }
}
