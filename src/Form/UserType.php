<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;
    /**
     * @var User
     */
    private $user;

    /**
     * UserType constructor.
     * @param Security $security
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(Security $security, AccessDecisionManagerInterface $decisionManager)
    {
        $this->security = $security;
        $this->decisionManager = $decisionManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Check if user to modify is Admin
        $this->user = ($options['data']);

        $builder
            ->add('username', null, [
                'label' => 'Nom d\'utilisateur'
            ])
            ->add('email')
            ->add('plainPassword', RepeatedType::class, $this->getPasswordFieldOptions());

        if ($this->security->isGranted("ROLE_ADMIN")) {
            $this->addAdminCheckbox($builder);
        }
        if ($this->security->isGranted('IS_ANONYMOUS')) {
            $this->addAgreeTermsCheckbox($builder);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    private function getPasswordFieldOptions()
    {
        return [
            'label' => 'Mot de passe',
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux mots de passe doivent correspondre.',
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrez un mot de passe',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ];
    }

    private function addAdminCheckbox(FormBuilderInterface $builder)
    {
        $builder->add('admin', CheckboxType::class, [
            'mapped' => false,
            'label' => 'Administrateur',
            'required' => false,
            'data' => $this->isAdmin(),
            'attr' => [
                'class' => 'switch'
            ],
        ]);
    }

    private function addAgreeTermsCheckbox(FormBuilderInterface $builder)
    {
        $builder->add('agreeTerms', CheckboxType::class, [
            'label' => 'J\'accepte les conditions d\'utilisation',
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter les conditions d\'utilisation',
                ]),
            ],
        ]);
    }

    private function isAdmin()
    {
        $token = new UsernamePasswordToken($this->user, 'none', 'none', $this->user->getRoles());
        return $this->decisionManager->decide($token, ['ROLE_ADMIN']);
    }
}
