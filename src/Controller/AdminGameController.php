<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/adminGame')]
class AdminGameController extends AbstractController
{

    #[Route('/', name: 'app_adminGame_index')]
    public function index( GameRepository $gameRepository): Response
    {

        return $this->render('Admin/index.html.twig', [
            'games' => $gameRepository->findAll(),
            
        ]);
    }

    #[Route('/new', name: 'adminGame_new')]
    public function new(Request $request, SluggerInterface $slugger , GameRepository $gameRepository ): Response
    {

        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('image')->getData();

            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $game->setImage($newFilename);
            }

            $gameRepository->save($game, true);
    
            return $this->redirectToRoute('app_adminGame_index');
        }
    
        return $this->renderForm('Admin/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

  
}