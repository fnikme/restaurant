<?php

namespace App\Controller;

use App\Entity\Restaurant;
use DateTimeImmutable;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    
    #[Route('', name: 'new', methods: ['GET', 'POST'])]
    /**
     *  @OA\Post(
    *     path="/api/restaurant",
     *     summary="Créer un restaurant",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du restaurant à créer",
     *         @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="name", type="string", example="Nom du restaurant"),
     *          @OA\Property(property="description", type="string", example="Description du restaurant"),
     *       )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Restaurant créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
     *             @OA\Property(property="description", type="string", example="Description du restaurant"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), type: Restaurant::class, format: 'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        $restaurant->setMaxGuest(50);
        $this->manager->persist($restaurant);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($restaurant, format: 'json');
        $location = $this->urlGenerator->generate(
            name:'app_api_restaurant_show',
            parameters: ['id' => $restaurant->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, status: Response::HTTP_CREATED, headers:["Location" => $location], json: true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    /**
     * @OA\Get(
     *     path="/api/restaurant/{id}",
     *     summary="Afficher un restaurant par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du restaurant à afficher",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant trouvé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
     *             @OA\Property(property="description", type="string", example="Description du restaurant"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant non trouvé"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, 'json');

            return new JsonResponse($responseData, json: true);
        }

        return new JsonResponse( data: null, status: Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if ($restaurant) {
            $restaurant = $this->serializer->deserialize(
                $request->getContent(),
                Restaurant::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );
            $restaurant->setUpdatedAt(new DateTimeImmutable());
            $this->manager->flush();

            return new JsonResponse( data: null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse( data: null, status: Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    /**
     * @OA\Delete(
     *     path="/api/restaurant/{id}",
     *     summary="Supprimer un restaurant par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du restaurant à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
     *             @OA\Property(property="description", type="string", example="Description du restaurant"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant non supprimé"
     *     )
     * )
     */
    public function delete(int $id): jsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if ($restaurant) {
            $this->manager->remove($restaurant);
            $this->manager->flush();

            return new JsonResponse( data: null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse( data: null, status: Response::HTTP_NOT_FOUND);
    }
}