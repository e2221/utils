<?php
declare(strict_types=1);

namespace e2221\utils\Html;


class HrefElement extends BaseElement
{

    public function __construct(string $elementName='a', array $attributes=[], ?string $textContent=null)
    {
        parent::__construct($elementName, $attributes, $textContent);
    }

    /**
     * Get static
     * @param string|null $elementName
     * @param array $attributes
     * @param string|null $textContent
     * @return HrefElement
     */
    public static function getStatic(?string $elementName='a', array $attributes=[], ?string $textContent=null): HrefElement
    {
        $static = new static($elementName);
        return $static
            ->addHtmlAttributes($attributes)
            ->setTextContent($textContent);
    }

    /**
     * Set link
     * @param string $link
     * @param array|null $query
     * @return HrefElement
     */
    public function setLink(string $link, array $query=null): self
    {
        $this->element->href($link, $query);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Set target
     * @param string $type
     * @return HrefElement
     */
    public function setTarget(string $type): HrefElement
    {
        $this->needsRerender = true;
        return $this->addHtmlAttribute('target', $type);
    }

    /**
     * Set target blank
     * @param bool $targetBlank
     * @return HrefElement
     */
    public function setTargetBlank(bool $targetBlank=true): HrefElement
    {
        $this->needsRerender = true;
        if($targetBlank === true)
             $this->setTarget('_blank');
        return $this;
    }

    /**
     * Set confirmation
     * @param string $confirmationText
     * @param string $event
     * @return $this
     */
    public function setConfirmation(string $confirmationText, string $event='onclick'): HrefElement
    {
        $this->needsRerender = true;
        $this->addHtmlAttribute($event, new Confirmation($confirmationText));
        return $this;
    }
}