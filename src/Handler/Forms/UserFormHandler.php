<?php

namespace App\Handler\Forms;

use App\Handler\Forms\AbstractFormHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFormHandler extends AbstractFormHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $formType;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * NewTrickFormHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }


    public function getFormType(): string
    {
        return $this->formType;
    }

    public function process($data): void
    {
        if ($this->getForm()->has('admin')) {
            if ($this->getForm()->get('admin')->getData()){
                $data->addRole('ROLE_ADMIN');
            } else {
                $data->removeRole('ROLE_ADMIN');
            }

        };
        $data->setPassword(
            $this->passwordEncoder->encodePassword(
                $data,
                $this->getForm()->get('plainPassword')->getData()
            )
        );
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function setFormType(?string $formType): void
    {
        $this->formType = $formType;
    }
}