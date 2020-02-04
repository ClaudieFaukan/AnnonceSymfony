<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompteurAccueilRepository")
 */
class CompteurAccueil
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $Visit;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_visit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisit(): ?int
    {
        return $this->Visit;
    }

    public function setVisit(?int $Visit): self
    {
        $this->Visit = $Visit;

        return $this;
    }

    public function getDateVisit(): ?\DateTimeInterface
    {
        return $this->date_visit;
    }

    public function setDateVisit(?\DateTimeInterface $date_visit): self
    {
        $this->date_visit = $date_visit;

        return $this;
    }
}
