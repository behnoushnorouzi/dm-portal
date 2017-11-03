<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\SuggestionStatus;
use AppBundle\Form\Type\SuggestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
            /** @var Suggestion $suggestion */
            $status = $this->get('query_service')->findOneOrException(SuggestionStatus::class, ['id' => 1]);

            /**@var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $suggestion->getFile();
            if($file) {
                $fileName = password_hash(uniqid(rand(), true), PASSWORD_DEFAULT);
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
        if (!$this->getUser()->isAdmin() && $suggestion->getUser() != $this->getUser() ) {
            throw new \Exception('Permission denied');
        }
        $filename = $suggestion->getFile().'.'.$suggestion->getFileExtension();

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
        $suggestionstatus = $this->get('query_service')->findOneOrException(SuggestionStatus::class, ['id' => $statusId]);
        $suggestion->setStatus($suggestionstatus);
        $this->get('query_service')->save($suggestion);

        return $this->redirectToRoute('get_suggestion', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param Suggestion  $request
     * @Route("/suggestions/edit/{id}", name="admin_suggestion_edit")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editSuggestionAction($id, Request $request, Suggestion $suggestion)
    {
        $em = $this->getDoctrine()->getManager();
        $suggestion = $this->get('query_service')->findOneOrException(Suggestion::class, ['id' => $id]);

        if (!$this->getUser()->isAdmin() && $suggestion->getUser() != $this->getUser()) {
            throw new \Exception('Permission denied');
        }

        $new_suggestion = new Suggestion();
        $new_suggestion->setFile($suggestion->getFile());
        $new_suggestion->setFileExtension($suggestion->getFileExtension());
        $new_suggestion->setFileMimeType($suggestion->getFileMimeType());

        $form = $this->createForm(SuggestionType::class, $suggestion, ['method' => 'PATCH']);
        $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Suggestion $suggestion */
                $status = $this->get('query_service')->findOneOrException(SuggestionStatus::class, ['id' => 1]);

                $suggestion->setUser($this->getUser());
                $suggestion->setStatus($status);

                $em->persist($suggestion);
                $em->flush();
            $this->addFlash('success', 'La modification a été bien enregistré');
            return $this->redirectToRoute('get_suggestion', ['id' => $suggestion->getId()]);
        }
        return $this->render('AppBundle:Suggestion:edit_suggestion.html.twig', [
            "form" => $form->createView(),
        ]);

    }


    /**
     * @Route("/suggestions/2/twitter", name="twitter_callback")
     *
     */
    public function postTweetWithoutMediaAction()
    {
       $a = $this->get('twitter_functions')->postTweetWithoutMedia();
       dump($a);die;
    }


}
