<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
     * Send Note as JSON in format { data: { note: { id, title, text, createdAt } } }
     *
     * @Route("/note/{id}", name="app_note_show", format="json", methods={"GET"})
     * @param $id
     * @param NoteRepository $noteRepository
     * @return JsonResponse
     */
    public function show($id, NoteRepository $noteRepository): JsonResponse
    {
        $note = $this->getNoteByIdOrThrowNotFoundException($id, $noteRepository);

        return $this->noteJsonResponse($note);
    }


    /**
     * @param Note $note
     * @return JsonResponse
     */
    private function noteJsonResponse(Note $note) : JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => ['note' => $note]
        ]);
    }


    /**
     * @param $id
     * @param NoteRepository $noteRepository
     * @return Note
     */
    private function getNoteByIdOrThrowNotFoundException($id, NoteRepository $noteRepository)
    {
        $note = $noteRepository->findOneBy(['id' => $id]);

        if (! $note) {
            $jsonErrorMessage = json_encode(['id' => 'This note does not exist!']);
            throw new NotFoundHttpException($jsonErrorMessage);
        }

        return $note;
    }
}
