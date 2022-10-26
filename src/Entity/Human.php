<?php

namespace App\Entity;

use App\Repository\HumanRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: HumanRepository::class)]
class Human extends Character
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
  protected $id;

  public function getId(): ?int
  {
    return $this->id;
  }
}
