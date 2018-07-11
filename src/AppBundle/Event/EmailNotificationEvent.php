<?php

namespace AppBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class EmailNotificationEvent extends Event
{
    protected $article;

    public function __construct($article)
    {
        $this->article = $article;
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param $article
     *
     * @return mixed
     */
    public function setArticle($article)
    {
        return $this->article = $article;
    }
}

