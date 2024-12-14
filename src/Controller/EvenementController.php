<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EvenementRepository; 


#[Route('/evenement')]
final class EvenementController extends AbstractController
{   

    #[Route('/front', name: 'evenement_front', methods: ['GET'])]
    public function afficherFront(EntityManagerInterface $entityManager): Response
    {   
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        
        return $this->render('evenement/evenementFront.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifiez la validité des dates
            if ($evenement->getDateDebut() >= $evenement->getDateFin()) {
                $this->addFlash('danger', 'La date de fin doit être postérieure à la date de début.');

                return $this->render('evenement/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Gestion de l'upload de l'image
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $evenement->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }


            // Sauvegarde de l'événement si tout est valide
            if ($form->isValid()) {
                $entityManager->persist($evenement);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement a été créé avec succès.');

                return $this->redirectToRoute('app_evenement_index');
            }
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/statistiques', name: 'evenement_statistiques')]
    public function statistiques(EvenementRepository $evenementRepository): Response
    {
        $averageDuration = $evenementRepository->findAverageDuration();
        $popularEventTypes = $evenementRepository->findPopularEventTypes();
        $nombreEvenementsFuturs = $evenementRepository->countFutureEvents();
    
        return $this->render('evenement/statistiques.html.twig', [
            'nombreEvenementsFuturs' => $nombreEvenementsFuturs,
            'averageDuration' => $averageDuration,
            'popularEventTypes' => $popularEventTypes,
        ]);

 }
}