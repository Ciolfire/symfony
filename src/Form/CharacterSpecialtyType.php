<?php

namespace App\Form;

use App\Entity\CharacterSpecialty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CharacterSpecialtyType extends AbstractType
{
  public $translator;

  public function __construct(TranslatorInterface $translator)
  {
      $this->translator = $translator;
  }


  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $translator = $this->translator;
    $builder
      ->add('name', null, [
        'attr' => [
          'data-character--creation-target' => 'specialties',
          'data-action' => 'character--creation#specialtyUpdate'
        ]
      ])
      ->add('skill', null, [
        'required' => true,
        'label' => 'skill.label',
        'group_by' => function($choice) use ($translator) {
          return $translator->trans($choice->getCategory(), [], 'character');
        },
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => CharacterSpecialty::class,
    ]);
  }
}
