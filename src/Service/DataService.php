<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Entity\Character;
use App\Entity\CharacterMerit;
use App\Entity\Chronicle;
use App\Entity\Devotion;
use App\Entity\Merit;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Vice;
use App\Entity\Virtue;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DataService
{
  private ManagerRegistry $doctrine;
  private ObjectManager $manager;
  private SluggerInterface $slugger;

  public function __construct(ManagerRegistry $doctrine, SluggerInterface $slugger)
  {
    $this->doctrine = $doctrine;
    $this->manager = $doctrine->getManager();
    $this->slugger = $slugger;
  }

  public function getDoctrine() : ManagerRegistry
  {
    return $this->doctrine;
  }

  public function getConnection(?string $name = null) : Connection
  {
    /** @var Connection */
    return $this->doctrine->getConnection($name);
  }

  /**
   * Add an entity, will add security checks there
   */
  public function add(object $entity) : void
  {
    $this->manager->persist($entity);
  }

  /**
   * Save an entity, will add security checks there
   */
  public function save(object $entity) : void
  {
    $this->manager->persist($entity);
    $this->flush();
  }

  /**
   * Remove an entity, will add security checks there
   */
  public function remove(object $entity) : void
  {
    $this->manager->remove($entity);
    $this->flush();
  }

  public function flush() : void
  {
    $this->manager->flush();
  }

  public function reset() : ObjectManager
  {
    return $this->doctrine->resetManager();
  }

  /**
   * @template T of object
   * @param class-string<T> $entity
   * @return ObjectRepository<object>
   */
  public function getRepository(string $entity) : ObjectRepository
  {
    return $this->doctrine->getRepository($entity);
  }

  /**
   * @template T of object
   * @param class-string<T> $class
   * @return T|null
   */
  public function find(string $class, mixed $id): ?object
  {
    $object = $this->getRepository($class)->find($id);

    if ($object instanceof $class) {
      return $object;
    } else {
      return null;
    }
  }

  /**
   * @template T of object
   * @param class-string<T> $class
   * @param array<string, mixed> $criteria>
   * @return T|null
  */
  public function findOneBy(string $class, array $criteria) : ?object
  {
    $object = $this->getRepository($class)->findOneBy($criteria);
    
    if ($object instanceof $class) {
      return $object;
    } else {
      return null;
    }
  }
  
  /**
   * @param class-string $class
   * @param array<string, mixed> $criteria
   * @param array<string, 'ASC'|'asc'|'DESC'|'desc'>|null $orderBy
   * @return array<int, object>
   */
  public function findBy(string $class, array $criteria, array $orderBy = null): array
  {

    return $this->getRepository($class)->findBy($criteria, $orderBy);
  }

  /**
   * @param class-string $class
   * @return array<int, object>
   */
  public function findAll(string $class) : array
  {

    return  $this->getRepository($class)->findAll();
  }

  public function upload(UploadedFile $file, string $target, string $fileName=null) : ?string
  {
    if (is_null($fileName)) {
      $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      $safeFilename = $this->slugger->slug($originalFilename);
      $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }

    try {
      $file->move($target, $fileName);
    } catch (FileException $e) {

      return null;
      // ... handle exception if something happens during file upload
    }

    return $fileName;
  }

  /** @return array<string> */
  public function getMeritTypes(Book $book = null) : array
  {
    $qb = $this->getConnection()->createQueryBuilder()
    ->select('type')
    ->from('merits')
    ->groupBy('type')
    ->andWhere("type != ''");
    if (is_null($book)) {
      $result = $qb->executeQuery()->fetchFirstColumn();
    } else {
      $result = $qb->andWhere('book_id = :id')
      ->setParameter('id', $book->getId())
      ->executeQuery()->fetchFirstColumn();
    }
    $result[] = "universal";
    
    return $result;
  }

  /** @return array<string> */
  public function getBookTypes(string $setting) : array
  {
    return $this->getConnection()->createQueryBuilder()
    ->select('type')
    ->from('book')
    ->where('setting = :setting')
    ->andWhere('type IS NOT NULL')
    ->groupBy('type')
    ->setParameter('setting', $setting)
    ->executeQuery()->fetchFirstColumn();
  }

  /** @return array<string> */
  public function getChronicleNotesCategory(Chronicle $chronicle, User $user) : array
  {
    return $this->getConnection()->createQueryBuilder()
    ->select('category')
    ->from('note')
    ->where('chronicle_id = :chronicle')
    ->andWhere('user_id = :user')
    ->groupBy('category')
    ->orderBy('category', 'ASC')
    ->setParameter('chronicle', $chronicle->getId())
    ->setParameter('user', $user->getId())
    ->executeQuery()->fetchFirstColumn();
  }

  /**
   * @return array<int, array<string, mixed>>
  **/
  public function getLinkableNotes(User $user, Note $note) : array
  {
    $chronicle = $note->getChronicle();

    if ($chronicle instanceof Chronicle) {
      
      return $this->getConnection()->createQueryBuilder()
      ->select('id, title')
      ->from('note')
      ->where('chronicle_id = :chronicle')
      ->andWhere('user_id = :user')
      ->andWhere('id != :note')
      ->orderBy('category_id', 'ASC')
      ->setParameter('chronicle', $chronicle->getId(), Types::INTEGER)
      ->setParameter('user', $user->getId(), Types::INTEGER)
      ->setParameter('note', $note->getId(), Types::INTEGER)
      ->executeQuery()->fetchAllAssociative();
    } else {

      return [];
    }
  }

  public function loadMeritsPrerequisites(mixed $merits, string $type=null) : void
  {
    switch ($type) {
      case 'character':
        /** @var CharacterMerit $charMerit */
        foreach ($merits as $charMerit) {
          if ($charMerit->getMerit() instanceof Merit) {
            $this->loadPrerequisites($charMerit->getMerit());
          }
        }
        break;

      default:
        /** @var Merit $merit */
        foreach ($merits as $merit) {
          if ($merit instanceof Merit) {
            $this->loadPrerequisites($merit);
          }
        }
        break;
    }
  }

  /**
   * @template T of Merit|Devotion
   * @param T $entity
   */
  public function loadPrerequisites(object $entity) : void
  {
    if (method_exists(get_class($entity), 'getPrerequisites')) {
      foreach ($entity->getPrerequisites() as $prerequisite) {
        $className = $prerequisite->getType();
        if (class_exists($className)) {
          $prereqEntity = $this->findOneBy($className, ['id' => $prerequisite->getEntityId()]);
          if (!is_null($prereqEntity)) {
            $prerequisite->setEntity($prereqEntity);
          }
        }
      }
    }
  }

  public function duplicateCharacter(Character $character, Chronicle $chronicle, User $user, string $path) : void
  {
    $oldId = $character->getId();
    // $vice = $character->getVirtue();
    // $virtue = $character->getVice();
    $this->doctrine->resetManager();
    $character->setId(null);
    $character->setChronicle($this->findOneBy(Chronicle::class, ['id' => $chronicle->getId()]));
    $character->setPlayer($this->findOneBy(User::class, ['id' => $user->getId()]));
    $character->setIsTemplate(false);
    $character->setIsNpc(true);

    // $vice = $this->findOneBy(Vice::class, ['id' => $vice]);

    // $character->setVice($vice);
    // $character->setVirtue($this->findOneBy(Virtue::class, ['id' => $virtue]));
    $this->manager->persist($character);
    $this->manager->flush();
    // Need to check how to properly import the avatar
    // $filesystem = new Filesystem();
    // $filesystem->copy("{$path}/{$oldId}", "{$path}/{$character->getId()}", true);
  }
}
