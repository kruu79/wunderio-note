<?php

namespace App\Controller;

use App\Entity\Note;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NoteController extends AbstractController
{
//    #[Route('/notes', name: 'note')]
//    public function index(): Response
//    {
//        return $this->render('note/index.html.twig', [
//            'controller_name' => 'NoteController',
//        ]);
//    }

    /**
     * Returns note as JSON
     *
     * @Route("/note/{id}", name="app_note_show", format="json", methods={"GET"})
     * @param Note $note
     * @return JsonResponse
     */
    public function show(Note $note): JsonResponse
    {
        return $this->noteJsonResponse($note);

    }


    private function noteJsonResponse($note)
    {
        return $this->json([
            'status' => 'success',
            'data' => ['note' => $note]
        ]);
    }
}
