<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class NoteController extends AbstractController
{
    /**
     * Returns an array of notes as JSON response in { data: { notes: [] } } property.
     * By default sorts results by createdAt property - newest first.
     *
     * Accepts parameters in query string:
     * sort=oldest to display oldest results first,
     * limit=int to limit result count,
     * search=search_query to search for notes with by text property content.
     *
     * @Route("/notes", name="app_note_index", methods={"GET"})
     * @param Request $request
     * @param NoteRepository $noteRepository
     * @return JsonResponse
     */
    public function index(Request $request, NoteRepository $noteRepository): JsonResponse
    {
        $params = [];

        $params['orderByCreatedAt'] = 'DESC'; // default sorting order - newest first
        if (strtolower($request->query->get('sort') === 'oldest')) {
            $params['orderByCreatedAt'] = 'ASC';
        };

        if ($limit = intval($request->query->get('limit'))) {
            $params['limit'] = $limit;
        }

        if ($search = trim($request->query->get('search'))) {
            $params['search'] = $search;
        }

        $notes = $noteRepository->findAllWithParameters($params);

        return $this->json([
            'status' => 'success',
            'data' => ['notes' => $notes]
        ]);
    }

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
     * Creates a new Note and sends it back as JSON response.
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
     * Deletes the Note.
     *
     * @Route("/note/{id}", name="app_note_delete", format="json", methods={"DELETE"})
     * @param $id
     * @param NoteRepository $noteRepository
     * @return JsonResponse
     */
    public function delete($id, NoteRepository $noteRepository): JsonResponse
    {
        $note = $this->getNoteByIdOrThrowNotFoundException($id, $noteRepository);
        $noteRepository->remove($note);

        return $this->json([
            'status' => 'success',
            'data' => null
        ]);
    }


    /**
     * @param Request $request
     * @param Note $note
     */
    private function setNoteTitleAndText(Request $request, Note $note): void
    {
        $params = json_decode($request->getContent(), true);

        if (isset($params['title'])) {
            $note->setTitle(trim($params['title']));
        }

        if (isset($params['text'])) {
            $note->setText(trim($params['text']));
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
