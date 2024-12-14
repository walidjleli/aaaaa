<?php

namespace App\Controller;

use App\Entity\TypeEvenement;
use App\Form\TypeEvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/evenement')]
final class TypeEvenementController extends AbstractController
{
    #[Route(name: 'app_type_evenement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $typeEvenements = $entityManager
            ->getRepository(TypeEvenement::class)
            ->findAll();

        return $this->render('type_evenement/index.html.twig', [
            'type_evenements' => $typeEvenements,
        ]);
    }

    #[Route('/new', name: 'app_type_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeEvenement = new TypeEvenement();
        $form = $this->createForm(TypeEvenementType::class, $typeEvenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeEvenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_evenement/new.html.twig', [
            'type_evenement' => $typeEvenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_evenement_show', methods: ['GET'])]
    public function show(TypeEvenement $typeEvenement): Response
    {
        return $this->render('type_evenement/show.html.twig', [
            'type_evenement' => $typeEvenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeEvenement $typeEvenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeEvenementType::class, $typeEvenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_evenement/edit.html.twig', [
            'type_evenement' => $typeEvenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_evenement_delete', methods: ['POST'])]
    public function delete(TypeEvenement $typeEvenement, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($typeEvenement);
        $entityManager->flush();

        // Renvoyer les types d'événements mis à jour
        $typeEvenements = $entityManager
            ->getRepository(TypeEvenement::class)
            ->findAll();

        // Rendre la même vue avec la liste mise à jour
        return $this->render('type_evenement/index.html.twig', [
            'type_evenements' => $typeEvenements,
            'message' => 'Le type d\'événement a été supprimé avec succès.',
        ]);
    }




}
