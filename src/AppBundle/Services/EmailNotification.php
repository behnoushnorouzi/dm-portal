<?php

namespace AppBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Twig\Template;

class EmailNotification
{
    protected $mailer;
    protected $entityManager;
    protected $translator;
    protected $template;

    public function __construct(\Swift_Mailer $mailer, EntityManager $entityManager, Translator $translator, Template $template)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->template = $template;
    }

    /**
     * @param $article
     * @throws \Throwable
     */
    public function notifyByEmail($article)
    {
        $users = $this->entityManager->getRepository('AppBundle:User')
            ->findModerators();

        foreach ($users as $user) {
            $message = (new \Swift_Message())
                ->setSubject($this->translator->trans('email.new-article.title'))
                ->setFrom('no-reply@darkmira.com', 'Darkmira')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->template->render('@App/Email/new_article.html.twig', [
                        'article' => $article
                    ]),
                    'text/html'
                );
            $this->mailer->send($message);
        }
    }
}
