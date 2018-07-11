<?php declare(strict_types=1);

namespace AppBundle\Services;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleVote;
use AppBundle\Repository\UserRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;
use Swift_Message;
use Swift_Mailer;

final class EmailNotification
{
    private $mailer;
    private $userRepository;
    private $translator;
    private $template;
    private $currentUser;

    public function __construct(
        Swift_Mailer $mailer,
        UserRepository $userRepository,
        Translator $translator,
        TwigEngine $template,
        TokenStorageInterface $currentUser
    )
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->template = $template;
        $this->currentUser = $currentUser;
    }

    /**
     * @param Article|null $article
     * @param ArticleVote|null $vote
     * @param string $templateName
     * @param string $translator
     * @throws \Twig\Error\Error
     */
    public function notifyByEmail(Article $article = null, ArticleVote $vote = null, string $templateName, string $translate)
    {
        if (empty($this->userRepository->findModerators())) {
            $users[] = $this->currentUser->getToken()->getUser();
        }

        $users = $this->userRepository->findModerators();

        foreach ($users as $user) {
            $message = (new Swift_Message())
                ->setSubject($this->translator->trans($translate))
                ->setFrom('no-reply@darkmira.com', 'Darkmira')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->template->render($templateName, [
                        'article' => ($article) ? $article :  $vote->getArticle(),
                        ($vote) ?: 'vote' => ($vote) ?: $vote,
                    ]),
                    'text/html'
                );
            $this->mailer->send($message);
        }
    }
}
