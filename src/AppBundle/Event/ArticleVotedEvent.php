<?php declare(strict_types=1);

namespace AppBundle\Event;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleVote;
use Symfony\Component\EventDispatcher\Event;

final class ArticleVotedEvent extends Event
{
    private $vote;
    private $article;

    public function __construct(ArticleVote $vote, Article $article)
    {
        $this->vote = $vote;
        $this->article = $article;
    }

    /**
     * @return ArticleVote
     */
    public function getVote(): ArticleVote
    {
        return $this->vote;
    }

    /**
     * @param ArticleVote $vote
     */
    public function setVote(ArticleVote $vote)
    {
        $this->vote = $vote;
    }

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;
    }
}
