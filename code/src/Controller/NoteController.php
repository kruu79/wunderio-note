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
     * Send Note as JSON in format { data: { note: { id, title, text, createdAt } } }.
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
     * Create a new Note and send it back in JSON format.
     *
     * @Route("/note/add", name="app_note_new", format="json", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @throws \Exception
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $note = new Note();

        $now = new DateTimeImmutable();
        $note->setCreatedAt($now);

        $title = trim($request->request->get('title'));
        $note->setTitle($title);

        $text = trim($request->request->get('text'));
        $note->setText($text);

        $errors = $validator->validate($note);

        if (count($errors)) {

            $errorArray = [];
            foreach($errors as $error) {
                $errorArray[$error->getPropertyPath()] = $error->getMessage();
            }

            $jsonErrorMessage = json_encode($errorArray);
            throw new UnprocessableEntityHttpException($jsonErrorMessage);
        }


        $entityManager->persist($note);
        $entityManager->flush();

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
