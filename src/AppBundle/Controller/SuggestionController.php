<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\SuggestionStatus;
use AppBundle\Entity\TwitterStatus;
use AppBundle\Form\Type\AdditionalDescriptionType;
use AppBundle\Form\Type\SuggestionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SuggestionController extends Controller
{

    /**
     * @Route("/suggestions", name="get_suggestions")
     * @Method({"GET"})
     * @return Response
     */
    public function getSuggestionsAction(): Response
    {
        $suggestions = $this->getDoctrine()->getManager()->getRepository(Suggestion::class)->findAll();

        return $this->render('AppBundle:Suggestion:get_suggestions.html.twig', ['suggestions' => $suggestions]);
    }

    /**
     * @Route("/suggestions/add", name="post_suggestions")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function postSuggestionsAction(Request $request): Response
    {
        $suggestion = new Suggestion();

        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $status = $this->get('query_service')->findOneOrException(SuggestionStatus::class, ['id' => 1]);
            $twitter_status = $this->get('query_service')->findOneOrException(TwitterStatus::class, ['id' => 1]);

            $file = $suggestion->getFile();
            if ($file) {
                $fileName = md5(uniqid());
                $extension = $file->guessExtension();
                $mimeType = $file->getMimeType();
                $file->move(
                    $this->getParameter('suggestion_directory'),
                    $fileName . '.' . $extension
                );
                $suggestion->setFileMimeType($mimeType);
                $suggestion->setFile($fileName);
                $suggestion->setFileExtension($extension);
            }
            $suggestion->setUser($this->getUser());
            $suggestion->setStatus($status);
            $suggestion->setTwitterStatus($twitter_status);
            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestion);
            $em->flush();

            return $this->redirect($this->generateUrl('get_suggestions'));
        }

        return $this->render('AppBundle:Suggestion:post_suggestions.html.twig', ["form" => $form->createView()]);
    }

    /**
     * @Route("/suggestions/{id}", name="get_suggestion")
     * @Method({"GET"})
     * @return Response
     */
    public function getSuggestionAction($id): Response
    {
        /** @var Suggestion $suggestion */
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);

        return $this->render('AppBundle:Suggestion:get_suggestion.html.twig', ['suggestion' => $suggestion]);
    }

    /**
     * @Route("/suggestions/download/{file}",name="get_suggestions_download")
     * @Method({"GET"})
     * @return Response
     */
    public function getSuggestionsDownloadAction($file)
    {

        /** @var Suggestion $suggestion */
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['file' => $file]);

        if (!$suggestion) {
            $this->suggestionNotFound();
        }

        $filename = $suggestion->getFile() . '.' . $suggestion->getFileExtension();

        $response = new Response();
        $response->headers->set('Content-type', $suggestion->getFileMimeType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->setContent(file_get_contents($this->getParameter('suggestion_directory') . '/' . $filename));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * @Route("/suggestions/{id}/status/{statusId}", name="get_suggestions_mark_as", requirements={
     *      "statusId": "2|3"
     * })
     * @Method({"GET"})
     */
    public function getSuggestionsMarkAsAction($id, $statusId)
    {
        $this->get('role_service')->adminOrException();
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);

        if (!$suggestion) {
            $this->suggestionNotFound();
        }
        $suggestionstatus = $this->get('query_service')->findOneOrException(SuggestionStatus::class, ['id' => $statusId]);
        $suggestion->setStatus($suggestionstatus);
        $this->get('query_service')->save($suggestion);

        return $this->redirectToRoute('get_suggestion', ['id' => $id]);
    }

    /**
     * @param $id
     * @param Request $request
     * @param Suggestion $suggestion
     * @Route("/suggestions/edit/{id}", name="admin_suggestion_edit")
     *
     * @return Response
     * @throws \Exception
     */
    public function editSuggestionAction($id, Request $request, Suggestion $suggestion): Response
    {
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);

        if (!$suggestion) {
            $this->suggestionNotFound();
        }

        $form = $this->createForm(AdditionalDescriptionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->get('role_service')->adminOrException();

            $suggestion->setAdditionalDescription($suggestion->getAdditionalDescription());
            $suggestion->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestion);
            $em->flush();

            return $this->redirectToRoute('get_suggestion', ['id' => $suggestion->getId()]);
        }
        return $this->render('AppBundle:Suggestion:edit_suggestion.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    public function postTwitterStatusAction($id, $statusId)
    {

        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);
        $twitterstatus = $this->get('query_service')->findOneOrException(TwitterStatus::class, ['id' => $statusId]);

        if (!$suggestion) {
            return $this->suggestionNotFound();
        }

        if (!$twitterstatus) {
            return $this->twitterstatusNotFound();
        }


        $suggestion->setTwitterStatus($twitterstatus);
        $this->get('query_service')->save($suggestion);

        return $this->redirectToRoute('get_suggestion', ['id' => $id]);
    }

    /**
     * @Route("/suggestions/tweet_with_media/{id}/status/{statusId}", name="tweet_withMedia"),  requirements={
     *      "statusId": "2"
     * @Method({"GET"})
     */
    public function postTweetWithMediaAction($id, $statusId): RedirectResponse
    {
        $this->get('role_service')->adminOrException();
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);

        if (!$suggestion) {
            $this->suggestionNotFound();
        }

        $file = $suggestion->getFile() .'.'. $suggestion->getFileExtension();

        //to make sure that the additional description exists
        if ($suggestion->getAdditionalDescription() != null) {

            $tweet = $suggestion->getAdditionalDescription();

        } else {

            $tweet = $suggestion->getDescription();
        }

        // To select the right method (with media OR without media)
        if ($suggestion->getFile() != null) {

            $media = $this->getParameter('suggestion_directory') . '/' . $file;
            $this->get('twitter_functions')->postTweetWithMedia($tweet, $media);

        } else {

            $this->get('twitter_functions')->postTweetWithoutMedia($tweet);
        }

        return $this->postTwitterStatusAction($id, $statusId);
    }

    /**
     * @return NotFoundHttpException
     */
    private function suggestionNotFound(): NotFoundHttpException
    {
        throw new NotFoundHttpException('Suggestion is not found!');
    }

    /**
     * @return NotFoundHttpException
     */
    private function twitterstatusNotFound(): NotFoundHttpException
    {
        throw new NotFoundHttpException('Twitter Status is not found!');
    }
}
