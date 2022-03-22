<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SkillRepository::class)
 */
class Skill
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=20)
   */
  private $name;

  /**
   * @ORM\Column(type="string", length=20)
   */
  private $category;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $fluff;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $description;

  public function __toString()
  {
    return "skill.{$this->name}";
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getCategory(): ?string
  {
    return $this->category;
  }

  public function setCategory(string $category): self
  {
    $this->category = $category;

    return $this;
  }

  public function getFluff(): ?string
  {
    return $this->fluff;
  }

  public function setFluff(?string $fluff): self
  {
    $this->fluff = $fluff;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }
}