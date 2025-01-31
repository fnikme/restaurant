<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private RestaurantRepository $repository) {}


    #[Route(name: 'new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        $restaurant = new Restaurant();
        $restaurant->setName(name: 'Quai Antique');
        $restaurant->setDescription(description: 'Cette qualité et ce goût par le chef Arnaud MICHANT.');
        $restaurant->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($restaurant);
        $this->manager->flush();

        return $this->json(
            ['message' => "Restaurant resource created with {$restaurant->getId()} id"],
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        // $restaurant CHERCHER RESTAURANT ID = 1
        if (!$restaurant) {
            throw new \Exception(message: "No Restaurant fount for {$id} id");
        }

        return $this->json(
            ['message' => "A Restaurant was fount : {$restaurant->getName()} for {$restaurant->getId()} id"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        // $restaurant = CHERCHER RESTAURANT ID = 1
        if (!$restaurant) {
            throw new \Exception(message: "No Restaurant fount for {$id} id");
        }

        $restaurant->setName('Restaurant name update');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_restaurant_show', ['id' => $restaurant->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // $restaurant = CHERCHER RESTAURANT ID =
        if (!$restaurant) {
            throw new \Exception(message: "No Restaurant fount for {$id} id");
        }

        return $this->json(['message' => 'Restaurant ressource deleded'], status: Response::HTTP_NO_CONTENT);
    }
}
