<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Merit;
use Doctrine\ORM\EntityManagerInterface;

class CharacterService
{
  private $doctrine;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->doctrine = $entityManager;
  }

  public function takeWound(Character $character, int $value)
  {
    $wounds = $character->getWounds();
    switch ($value) {
      case 1:
        $wounds['B']++;
        break;
      case 2:
        if ($wounds['B'] > 0) {
          $wounds['B']--;
          $wounds['L']++;
        }
        break;
      case 3:
        if ($wounds['L'] > 0) {
          $wounds['L']--;
          $wounds['A']++;
        }
        break;
      default:
        if ($wounds['A'] > 0) {
          $wounds['A']--;
        }
        break;
    }
    $character->setWounds($wounds);
    $this->doctrine->flush();
  }

  public function healWound(Character $character, int $value)
  {
    $wounds = $character->getWounds();
    switch ($value) {
      case 0:
        if ($wounds['B'] > 0) {
          $wounds['B']--;
        }
        break;
      case 1:
        if ($wounds['L'] > 0) {
          $wounds['L']--;
          $wounds['B']++;
        }
        break;
      case 2:
        if ($wounds['A'] > 0) {
          $wounds['A']--;
          $wounds['L']++;
        }
        break;
    }
    $character->setWounds($wounds);
    $this->doctrine->flush();
  }

  public function updateTrait(Character $character, $data)
  {
    switch ($data->trait) {
      case 'willpower':
        if ($data->value == 1) {
          $character->setCurrentWillpower(min($character->getWillpower(), $character->getCurrentWillpower() + 1));
          $this->doctrine->flush();
        } else if ($data->value == 0) {
          $character->setCurrentWillpower(max(0, $character->getCurrentWillpower() - 1));
          $this->doctrine->flush();
        }
        break;
      default:
        # code...
        break;
    }
  }

  public function updateExperience(Character $character, $data)
  {
    if ($data->method == "add") {
      $total = $character->getXpTotal();
      $new = $total + $data->value;
      $character->setXpTotal($new);
      $this->doctrine->flush();

      return $new;
    }
  }

  public function filterMerits(Character $character)
  {
    $merits = $this->doctrine->getRepository(Merit::class)->findAll();

    foreach ($merits as $key => $merit) {
      /** @var Merit $merit */
      if ($merit->getIsUnique() && $character->hasMerit($merit->getId())) {
        // Character already has this merit, we remove it from the list
        unset($merits[$key]);
      } else if ($merit->getIsCreationOnly()) {
        // Level 1 merit
        unset($merits[$key]);
      }
      // else if not right race
    }
    return $merits;
  }

}