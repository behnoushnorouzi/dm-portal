<?php

namespace AppBundle\Listener;


use AppBundle\Event\DmPortalEvents;
use AppBundle\Event\EmailNotificationEvent;
use AppBundle\Services\EmailNotification;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageListener implements EventSubscriberInterface
{
    protected $notification;

    public function __construct(EmailNotification $notification)
    {
        $this->notification = $notification;
    }

    public static function getSubscribedEvents()
    {
        return [ DmPortalEvents::EMAIL_NOTIFICATION => 'processMessage'];
    }

    /**
     * @param EmailNotificationEvent $event
     * @throws \Throwable
     */
    public function processMessage(EmailNotificationEvent $event)
    {
        try {
            $this->notification->notifyByEmail($event->getArticle());
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

}
