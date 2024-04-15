<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\GameRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $reservationRepository, GameRepository $gameRepository): Response
    {
        // Create a new Reservation instance
        $reservation = new Reservation();

        // Create a form for the Reservation entity
        $form = $this->createForm(ReservationType::class, $reservation);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current date for the reservation
            $reservation->setDate(new \DateTime());

            // Save the reservation to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Redirect to a success page or display a success message
            $this->addFlash('success', 'Reservation successfully established!');
            return $this->redirectToRoute('app_reservation_index');
        }

        // Render the form
        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'reservations' => $reservationRepository->findAll(),
            'games' => $gameRepository->findAll(),
        ]);
    }

    #[Route('/establish', name: 'app_reservation_establish', methods: ['POST'])]
    public function establish(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $gameId = $request->request->get('game_id');
        $game = $this->getDoctrine()->getRepository(Game::class)->find($gameId);

        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        // Create a new reservation
        $reservation = new Reservation();
        $reservation->setDate(new \DateTime());
        $reservation->setGame($game);

        // Persist the reservation
        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reservation successfully established'], Response::HTTP_OK);
    }
}
