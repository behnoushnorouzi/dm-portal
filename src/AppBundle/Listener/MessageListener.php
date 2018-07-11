<?php
namespace AppBundle\Listener;

use AppBundle\Event\ArticleMailStatusEvent;
use AppBundle\Event\ArticleVotedEvent;
use AppBundle\Event\DmPortalEvents;
use AppBundle\Event\ArticleSubmittedEvent;
use AppBundle\Services\EmailNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MessageListener implements EventSubscriberInterface
{
    private $notification;

    public function __construct(EmailNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DmPortalEvents::ARTICLE_SUBMITTED => 'processArticleSubmittedMessage',
            DmPortalEvents::ARTICLE_VOTED => 'processArticleVotedMessage',
            DmPortalEvents::ARTICLE_MAIL_STATUS => 'processArticleMailStatusMessage',
        ]
        ;
    }

    /**
     * @param ArticleSubmittedEvent $event
     * @throws \Throwable
     */
    public function processArticleSubmittedMessage(ArticleSubmittedEvent $event)
    {
        if (empty($event->getArticle())) {
            return ;
        }

        $this->notification->notifyByEmail($event->getArticle(), null ,'@App/Email/new_article.html.twig', 'email.new-article.title');
    }

    /**
     * @param ArticleVotedEvent $event
     * @throws \Twig\Error\Error
     */
    public function processArticleVotedMessage(ArticleVotedEvent $event)
    {
        if (empty($event->getArticle())) {
            return ;
        }

        $this->notification->notifyByEmail($event->getArticle(), $event->getVote() ,'@App/Email/vote_article.html.twig', 'email.vote-article.title');
    }

    /**
     * @param ArticleMailStatusEvent $event
     * @throws \Twig\Error\Error
     */
    public function processArticleMailStatusMessage(ArticleMailStatusEvent $event)
    {
        if (empty($event->getArticle())) {
            return ;
        }

        $this->notification->notifyByEmail($event->getArticle(), null ,'@App/Email/status_article.html.twig', 'email.status-article.title');
    }

}
