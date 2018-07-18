<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Id\TableGenerator;
use Doctrine\ORM\Mapping as ORM;

/**
 * LinkedinStatus
 *
 * @ORM\Table(name="linkedin_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkedinStatusRepository")
 */
class LinkedinStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idLinkedIn;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100)
     *
     */
    private $status = 1;

    /**
     * Get idLinkedIn
     *
     * @return int
     */
    public function getIdLinkedIn(): int
    {
        return $this->idLinkedIn;
    }

    /**
     * @param $idLinkedIn
     * @return TableGenerator
     */
    public function setIdLinkedIn(int $idLinkedIn): LinkedinStatus
    {
        $this->idLinkedIn = $idLinkedIn;

        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return LinkedinStatus
     */
    public function setStatus(string $status): LinkedinStatus
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
