<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Homebrewable;
use App\Entity\Traits\Sourcable;
use App\Repository\DisciplineRepository;
use App\Entity\Translation\DisciplineTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\HTMLToMarkdown\HtmlConverter;

#[ORM\AssociationOverrides([new ORM\AssociationOverride(name: "book", inversedBy: "disciplines"), new ORM\AssociationOverride(name: "homebrewFor", inversedBy: "disciplines")])]
#[ORM\Entity(repositoryClass: DisciplineRepository::class)]
#[Gedmo\TranslationEntity(class: DisciplineTranslation::class)]
class Discipline
{
  use Homebrewable;
  use Sourcable;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  private ?int $id;

  #[Gedmo\Locale]
  private string $locale = "en";

  #[Gedmo\Translatable]
  #[ORM\Column(type: Types::STRING, length: 50)]
  private string $name;

  #[Gedmo\Translatable]
  #[ORM\Column(type: Types::TEXT)]
  private string $description;

  #[Gedmo\Translatable]
  #[ORM\Column(type: Types::STRING, length: 90, nullable: true)]
  private string $short;

  #[ORM\Column(type: Types::BOOLEAN)]
  private bool $isRestricted = true;

  #[Gedmo\Translatable]
  #[ORM\Column(type: Types::TEXT)]
  private string $rules;

  #[ORM\OneToMany(targetEntity: DisciplinePower::class, mappedBy: "discipline", orphanRemoval: true)]
  #[ORM\OrderBy(["level" => "ASC", "name" => "ASC"])]
  private Collection $powers;

  #[ORM\Column]
  private ?bool $isThaumaturgy;

  #[ORM\Column]
  private ?bool $isSorcery;

  #[ORM\Column]
  private ?bool $isCoil;

  public function __construct(bool $sorcery = false, bool $thaumaturgy = false, bool $coil = false)
  {
    $this->isSorcery = $sorcery;
    $this->isThaumaturgy = $thaumaturgy;
    $this->isCoil = $coil;
    $this->powers = new ArrayCollection();
  }

  public function __toString()
  {
    return $this->name;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function isSimple(): bool {
    if ($this->isThaumaturgy() || $this->isCoil() || $this->isSorcery()) {

      return false;
    }

    return true;
  }

  public function IsSinglePower(): bool
  {
    if (count($this->getPowers()) == 1) {
      return true;
    }
    return false;
  }

  public function getPower(): ?DisciplinePower
  {
    if ($this->IsSinglePower()) {

      return $this->powers->first();
    } else return null;
  }

  public function getRitual(): ?DisciplinePower
  {
    if ($this->isSorcery()) {
      foreach ($this->powers as $power) {
        /** @var DisciplinePower $power */
        if ($power->getLevel() == 0) {

          return $power;
        }
      }
    }

    return null;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getShort(): ?string
  {
    return $this->short;
  }

  public function setShort(string $short = ""): self
  {
    $this->short = $short;

    return $this;
  }

  public function isRestricted(): ?bool
  {
    return $this->isRestricted;
  }

  public function setIsRestricted(bool $isRestricted): self
  {
    $this->isRestricted = $isRestricted;

    return $this;
  }

  public function getRules(): ?string
  {
    return $this->rules;
  }

  public function setRules(string $rules = ""): self
  {
    $converter = new HtmlConverter();
    $rules = $converter->convert($rules);
    $this->rules = $rules;

    return $this;
  }

  /**
   * @return Collection|DisciplinePower[]
   */
  public function getPowers(): Collection
  {
    return $this->powers;
  }

  public function addPower(DisciplinePower $power): self
  {
    if (!$this->powers->contains($power)) {
      $this->powers[] = $power;
      $power->setDiscipline($this);
    }

    return $this;
  }

  public function removePower(DisciplinePower $power): self
  {
    if ($this->powers->removeElement($power)) {
      // set the owning side to null (unless already changed)
      if ($power->getDiscipline() === $this) {
        $power->setDiscipline(null);
      }
    }

    return $this;
  }

  public function getMaxLevel(): int
  {
    $max = 0;
    foreach ($this->powers as $power) {
      /** @var DisciplinePower $power */
      if ($power->getLevel() > $max) {
        $max = $power->getLevel();
      }
    }

    return $max;
  }

  public function isThaumaturgy(): ?bool
  {
    return $this->isThaumaturgy;
  }

  public function setIsThaumaturgy(bool $isThaumaturgy): self
  {
    $this->isThaumaturgy = $isThaumaturgy;

    return $this;
  }

  public function isCreationUnlocked() : bool
  {
    if (!$this->isRestricted) {
      if (!in_array($this->id, [2, 4, 5, 6, 8])) {

        return false;
      }
    }

    return true;
  }

  public function isSorcery(): ?bool
  {
    return $this->isSorcery;
  }

  public function setIsSorcery(bool $isSorcery): self
  {
    $this->isSorcery = $isSorcery;

    return $this;
  }

  public function isCoil(): ?bool
  {
    return $this->isCoil;
  }

  public function setIsCoil(bool $isCoil): self
  {
    $this->isCoil = $isCoil;

    return $this;
  }
}
