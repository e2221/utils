<?php


namespace e2221\utils\Html;


use Nette\SmartObject;

class Confirmation
{
    use SmartObject;

    protected string $confirmationText='';

    public function __construct(string $confirmationText)
    {
        $this->confirmationText = $confirmationText;
    }

    public function getConfirmation(?string $confirmationText=null): string
    {
        return sprintf("return confirm(''%s)", $confirmationText ?? $this->confirmationText);
    }

    public function __toString()
    {
        return $this->getConfirmation($this->confirmationText);
    }
}