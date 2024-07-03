<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Merit;
use App\Form\Type\ContentTypeType;
use App\Form\Type\SourceableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Twig\Extra\Markdown\LeagueMarkdown;

class MeritType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $converter = new LeagueMarkdown();
    /** @var Merit $merit */
    $merit = $options['data'];

    $builder
      ->add('name', null, ['label' => "label.single"])
      ->add('source', SourceableType::class, [
        'data_class' => Merit::class,
        'label' => 'source.label',
      ])
      ->add('type', ContentTypeType::class, [
        'data_class' => Merit::class,
        'label' => false,
      ])
      ->add('category', ChoiceType::class, [
        'label' => "category.label",
        'choices' => [
          'category.mental' => 'mental',
          'category.physical' => 'physical',
          'category.social' => 'social',
        ],
      ])
      ->add('description', null, ['label' => 'description', 'help' => 'help.description'])
      ->add('effect', CKEditorType::class, ['label' => "effect", 'empty_data' => '', 'data' => $converter->convert($merit->getEffect())])
      ->add('min', null, ['label' => "min"])
      ->add('max', null, ['label' => "max"])
      ->add('isCreationOnly', null, ['label' => "creation", 'help' => "help.creation"])
      ->add('isUnique', null, ['label' => "unique", 'help' => "help.unique"])
      ->add('isExpanded', null, ['label' => "expanded", 'help' => "help.expanded"])
      ->add('isFighting', null, ['label' => "fighting", 'help' => "help.fighting"])
      ->add('prerequisites', CollectionType::class, [
        'label' => false,
        'entry_type' => PrerequisiteType::class,
        'entry_options' => ['label' => false, 'type' => 'merit'],
        'allow_add' => true,
        'allow_delete' => true,
      ])
      ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Merit::class,
      'translation_domain' => 'merit',
    ]);
  }
}
