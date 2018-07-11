<?php
namespace AppBundle\Event;

use AppBundle\Entity\Article;
use Symfony\Component\EventDispatcher\Event;

class ArticleMailStatusEvent extends Event
{
    private $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article)
    {
        $this->article = $article;
    }

}
