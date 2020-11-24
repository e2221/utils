<?php


namespace e2221\utils\Html;


class HrefElement extends BaseElement
{

    public function __construct(string $elementName='a', array $attributes=[], ?string $textContent=null)
    {
        parent::__construct($elementName, $attributes, $textContent);
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
     * Set target blank
     * @param string $type
     * @return HrefElement
     */
    public function setTarget(string $type='_blank'): HrefElement
    {
        return $this->addHtmlAttribute('target', $type);
    }

    /**
     * Set confirmation
     * @param string $confirmationText
     * @param string $event
     * @return $this
     */
    public function setConfirmation(string $confirmationText, string $event='onclick'): HrefElement
    {
        $this->addHtmlAttribute($event, new Confirmation($confirmationText));
        return $this;
    }
}