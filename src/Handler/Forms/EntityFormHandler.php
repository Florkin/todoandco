<?php

namespace App\Handler\Forms;

use App\Handler\Forms\AbstractFormHandler;
use Doctrine\ORM\EntityManagerInterface;

class EntityFormHandler extends AbstractFormHandler
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
     * NewTrickFormHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function getFormType(): string
    {
        return $this->formType;
    }

    public function process($data): void
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function setFormType(?string $formType): void
    {
        $this->formType = $formType;
    }
}