<?php

namespace App\Handler\Forms;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormHandler
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormInterface
     */
    private $form;


    /**
     * @param mixed $formFactory
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function handle(Request $request, $data, $formType): bool
    {
        $this->setFormType($formType);
        $this->form = $this->formFactory->create($this->getFormType(), $data)->handleRequest($request);
//        dd($this->form);
        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->process($data);
            return true;
        }

        return false;
    }

    public function createView()
    {
        return $this->form->createView();
    }

    protected function getForm(): FormInterface
    {
        return $this->form;
    }

    abstract public function getFormType(): string;
    abstract public function setFormType(string $formType): void;
    abstract public function process($data): void;

}