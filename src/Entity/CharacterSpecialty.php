<?php

namespace App\Entity;

use App\Repository\CharacterSpecialtyRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CharacterSpecialtyRepository::class)]
#[ORM\Table(name: "characters_specialty")]
class CharacterSpecialty
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
  private $id;

  #[ORM\Column(type: "string", length: 30)]
  private $name;

  #[ORM\ManyToOne(targetEntity: Character::class, inversedBy: "specialties", cascade: ["persist", "remove"])]
  private $character;

  #[ORM\ManyToOne(targetEntity: Skill::class)]
  private $skill;


  public function __construct(Character $character = null, Skill $skill = null, string $name = null)
  {
    $this->character = $character;
    $this->skill = $skill;
    $this->name = $name;
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

  public function getCharacter(): ?Character
  {
    return $this->character;
  }

  public function setCharacter(?Character $character): self
  {
    $this->character = $character;

    return $this;
  }

  public function getSkill(): ?Skill
  {
    return $this->skill;
  }

  public function setSkill(?Skill $skill): self
  {
    $this->skill = $skill;

    return $this;
  }
}
