<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
     * Sends Note as JSON response in format { data: { note: { id, title, text, createdAt } } }.
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
     * Creates a new Note and sends it back in as JSON response.
     *
     * @Route("/note/add", name="app_note_new", format="json", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param NoteRepository $noteRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function new(
        Request $request,
        ValidatorInterface $validator,
        NoteRepository $noteRepository
    ): JsonResponse
    {
        $note = new Note();

        $now = new DateTimeImmutable();
        $note->setCreatedAt($now);

        $this->setNoteTitleAndText($request, $note);
        $this->validateNote($note, $validator);
        $noteRepository->add($note);

        return $this->noteJsonResponse($note);
    }


    /**
     * Edits text and title of existing Note and sends it back as JSON response.
     *
     * @Route("/note/{id}", name="app_note_edit", format="json", methods={"PUT"})
     * @param $id
     * @param NoteRepository $noteRepository
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function edit(
        $id,
        NoteRepository $noteRepository,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $note = $this->getNoteByIdOrThrowNotFoundException($id, $noteRepository);

        $this->setNoteTitleAndText($request, $note);
        $this->validateNote($note, $validator);
        $noteRepository->add($note);

        return $this->noteJsonResponse($note);
    }


    /**
     * @param Request $request
     * @param Note $note
     */
    private function setNoteTitleAndText(Request $request, Note $note): void
    {
        $title = $request->request->get('title');
        if ($title !== null) {
            $note->setTitle(trim($title));
        }

        $text = $request->request->get('text');
        if ($text !== null) {
            $note->setText(trim($text));
        }
    }


    /**
     * @param Note $note
     * @param ValidatorInterface $validator
     * @throws UnprocessableEntityHttpException if $note has validation error(s)
     */
    private function validateNote(Note $note, ValidatorInterface $validator): void
    {
        $errors = $validator->validate($note);

        if (count($errors)) {

            $errorArray = [];
            foreach($errors as $error) {
                $errorArray[$error->getPropertyPath()] = $error->getMessage();
            }

            $jsonErrorMessage = json_encode($errorArray);
            throw new UnprocessableEntityHttpException($jsonErrorMessage);
        }
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
     * @throws NotFoundHttpException if Note with $id does not exist
     * @return Note
     */
    private function getNoteByIdOrThrowNotFoundException($id, NoteRepository $noteRepository): Note
    {
        $note = $noteRepository->find($id);

        if (! $note) {
            $jsonErrorMessage = json_encode(['id' => 'This note does not exist!']);
            throw new NotFoundHttpException($jsonErrorMessage);
        }

        return $note;
    }
}
