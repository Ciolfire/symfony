<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Character;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CharacterVoter extends Voter
{
  public const VIEW = 'view';
  public const EDIT = 'edit';
  public const DELETE = 'delete';

  public function __construct(private Security $security) {}

  protected function supports(string $attribute, mixed $subject): bool
  {
    return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
      && $subject instanceof Character;
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
  {
    /** @var Character $subject */
    $user = $token->getUser();
    if ($this->security->isGranted('ROLE_GM')) {
      return true;
    }

    if (!$user instanceof User && !$subject->isPremade()) {
      // if the user is anonymous, do not grant access
      return false;
    }

    switch ($attribute) {
      case self::DELETE:
        return $this->canDelete($subject, $user);
        break;
      case self::EDIT:
        return $this->canEdit($subject, $user);
        break;
      case self::VIEW:
        return $this->canView($subject, $user);
        break;
      default:
        throw new \LogicException('This code should not be reached!');
    }

    return false;
  }

  private function canView(Character $character, User $user): bool
  {
    if ($this->canEdit($character, $user)) {
      return true;
    }

    if ($character->isPremade()) {
      return true;
    }

    return false;
  }

  private function canEdit(Character $character, User $user): bool
  {
    // The player own the character
    if ($user === $character->getPlayer()) {

      return true;
    }
    // The storyteller can edit the character
    if (!is_null($character->getChronicle()) && $user === $character->getChronicle()->getStoryteller()) {

      return true;
    }

    return false;
  }

  private function canDelete(Character $character, User $user): bool
  {
    if ($user === $character->getPlayer()) {

      return true;
    }

    return false;
  }
}
