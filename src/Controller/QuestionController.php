<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function ask(Request $request, EntityManagerInterface $em): Response
    {
        $question = new Question();
        $formQuestion = $this->createForm(QuestionType::class, $question);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
            $question->setNbrOfResponse(0);
            $question->setRating(0);
            $question->setCreatedAt(new \DateTimeImmutable());

            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Votre question a été ajoutée');

            return $this->redirectToRoute('home');
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion,
        ]);
    }

    #[Route('/question/{id}', name: 'question_show', requirements: ['id' => '\d+'])]
    public function show(
        Request $request,
        #[MapEntity(id: 'id')] Question $question,
        EntityManagerInterface $em
    ): Response {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setRating(0);
            $comment->setQuestion($question);

            $question->setNbrOfResponse(($question->getNbrOfResponse() ?? 0) + 1);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Votre réponse a bien été ajoutée');

            return $this->redirect($request->getUri());
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'form' => $commentForm,
        ]);
    }

    #[Route('/question/rating/{id}/{score}', name: 'question_rating', requirements: ['id' => '\d+', 'score' => '-?\d+'])]
    public function ratingQuestion(
        Request $request,
        #[MapEntity(id: 'id')] Question $question,
        int $score,
        EntityManagerInterface $em
    ): Response {
        $question->setRating(($question->getRating() ?? 0) + $score);
        $em->flush();

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }

    #[Route('/comment/rating/{id}/{score}', name: 'comment_rating', requirements: ['id' => '\d+', 'score' => '-?\d+'])]
    public function ratingComment(
        Request $request,
        #[MapEntity(id: 'id')] Comment $comment,
        int $score,
        EntityManagerInterface $em
    ): Response {
        $comment->setRating(($comment->getRating() ?? 0) + $score);
        $em->flush();

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }
}
