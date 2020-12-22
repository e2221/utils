<?php
declare(strict_types=1);

namespace e2221\utils\Html;

use e2221\utils\Html\Exceptions\BaseElementException;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nette\Utils\IHtmlString;


class BaseElement
{
    use SmartObject;

    /** @var string|null Html Element name */
    protected ?string $elementName=null;

    /** @var Html Nette html element */
    protected Html $element;

    /** @var Html|null Rendered html element */
    protected ?Html $render=null;

    /** @var bool  */
    protected bool $needsRerender=true;

    /** @var bool Is element hidden? */
    public bool $hidden=false;

    /** @var string Default - no changeable class */
    public string $defaultClass='';

    /** @var string Class - will be connected to default class */
    protected string $class='';

    /** @var BaseElement|null Parent element */
    protected ?BaseElement $parent=null;

    /** @var BaseElement[]|IHtmlString[] */
    protected array $childrenElements=[];

    public function __construct(?string $elementName=null, array $attributes=[], ?string $textContent=null)
    {
        $this->element = Html::el($elementName ?? $this->elementName);
        if(count($attributes) > 0)
            $this->addHtmlAttributes($attributes);
        if(is_string($textContent))
            $this->setTextContent($textContent);
    }

    public function __toString(): string
    {
        return (string)$this->render();
    }

    /**
     * Prepare element to render-able state
     */
    public function prepareElement(): void
    {
        $this->render();
    }

    /**
     * Function that will be called before each render
     */
    public function beforeRender(): void
    {
    }

    /**
     * Render element
     * @return Html|null
     */
    public function render(): ?Html
    {
        $this->beforeRender();
        if($this->hidden === true)
            return null;
        if($this->needsRerender === false && $this->render instanceof Html)
            return $this->element;

        foreach($this->childrenElements as $element)
        {
            if($element instanceof IHtmlString || is_string($element))
                $this->element->addHtml($element);
            if($element instanceof BaseElement)
            {
                $element->setParent($this);
                $this->element->addHtml(($element->render()));
            }
        }

        $class = $this->buildElementClass();
        if(empty($class) === false)
            $this->element->class($class);

        $this->needsRerender = false;
        return $this->render = $this->element;
    }

    /**
     * Get static
     * @param string|null $elementName
     * @param array $attributes
     * @param string|null $textContent
     * @return BaseElement
     */
    public static function getStatic(?string $elementName=null, array $attributes=[], ?string $textContent=null): BaseElement
    {
        $static = new static($elementName);
        return $static
            ->addHtmlAttributes($attributes)
            ->setTextContent($textContent);
    }

    /**
     * Render start tag
     * @return string|null
     */
    public function renderStartTag(): ?string
    {
        if($this->needsRerender === false && $this->render instanceof Html)
            return $this->render->startTag();
        $render = $this->render();
        if($render instanceof Html)
            return $render->startTag();
        return null;
    }

    /**
     * Start tag - the same as renderStartTag() for compatibility with \Nette\Utils\Html
     * @return string|null
     */
    public function startTag(): ?string
    {
        return $this->renderStartTag();
    }

    /**
     * Render end tag
     * @return string|null
     */
    public function renderEndTag(): ?string
    {
        if($this->needsRerender === false && $this->render instanceof Html)
            return $this->render->endTag();
        $render = $this->render();
        if($render instanceof Html)
            return $render->endTag();
        return null;
    }

    /**
     * End tag - the same as renderEndTag() for compatibility with \Nette\Utils\Html
     * @return string|null
     */
    public function endTag(): ?string
    {
        return $this->renderEndTag();
    }

    /**
     * Add element
     * @param string $name
     * @param IHtmlString|BaseElement $element
     * @return BaseElement
     */
    public function addElement(string $name, $element): BaseElement
    {
        $this->childrenElements[$name] = $element;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Get children elements
     * @return BaseElement[]|IHtmlString[]
     */
    public function getChildrenElements()
    {
        return $this->childrenElements;
    }

    /**
     * Get children element
     * @param string $name
     * @return BaseElement|IHtmlString
     * @throws BaseElementException
     */
    public function getChildrenElement(string $name)
    {
        if(isset($this->childrenElements[$name]) === false)
            throw new BaseElementException(sprintf('Child element [%s] was not found.', $name));
        return $this->childrenElements[$name];
    }

    /**
     * Set text content
     * @param Html|string|int|float $textContent
     * @return $this
     */
    public function setTextContent($textContent): self
    {
        $this->element->setText($textContent);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add text content
     * @param Html|string|float|int $textContent
     * @return BaseElement
     */
    public function addTextContent($textContent): self
    {
        $this->element->addText($textContent);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add title
     * @param string|null $title
     * @return BaseElement
     */
    public function addTitle(?string $title=null): self
    {
        $this->element->title($title);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add Span
     * @param string|null $spanClass
     * @param array $attributes [html attributes]
     * @param bool $removeTextContent [true => text content will be deleted]
     * @return Html
     */
    public function addSpanElement(?string $spanClass=null, array $attributes=[], bool $removeTextContent=false): Html
    {
        $span = Html::el('span', $attributes)
            ->class($spanClass);
        if($removeTextContent === true)
            $this->element->setText('');
        $this->element->addHtml($span);
        $this->needsRerender = true;
        return $span;
    }

    /**
     * Add Icon
     * @param string|null $iconClass
     * @param array $attributes
     * @param bool $removeTextContent [true => text content will be deleted]
     * @return Html
     */
    public function addIconElement(?string $iconClass=null, array $attributes=[], bool $removeTextContent=false): Html
    {
        $icon = Html::el('i', $attributes)
            ->class($iconClass);
        if($removeTextContent === true)
            $this->element->setText('');
        $this->element->addHtml($icon);
        $this->needsRerender = true;
        return $icon;
    }

    /**
     * Add html attribute
     * @param string $attributeName
     * @param string|null $attributeValue
     * @return BaseElement
     */
    public function addHtmlAttribute(string $attributeName, $attributeValue=null): self
    {
        $this->element->setAttribute($attributeName, $attributeValue);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add attributes
     * @param array $attributes
     * @return BaseElement
     */
    public function addHtmlAttributes(array $attributes): self
    {
        $this->element->addAttributes($attributes);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add data attribute
     * @param string $attributeName
     * @param string|null $attributeValue
     * @return BaseElement
     */
    public function addDataAttribute(string $attributeName, $attributeValue=null): self
    {
        $this->element->data($attributeName, $attributeValue ?? '');
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add data attributes
     * @param array $attributes
     * @return BaseElement
     */
    public function addDataAttributes(array $attributes): self
    {
        foreach($attributes as $attribute => $value)
            $this->addDataAttribute($attribute, $value);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Build element class
     * @return string
     */
    public function buildElementClass(): string
    {
        return ltrim(rtrim(sprintf("%s%s",
            empty($this->defaultClass) ? '' : $this->defaultClass . ' ',
            empty($this->class) ? '' : $this->class . ' ',
        )));
    }

    /**
     * Set default element
     * @param Html $element
     * @return BaseElement
     */
    public function setElement(Html $element): self
    {
        $this->element = $element;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Change element name
     * @param string $name
     * @return $this
     */
    public function setElementName(string $name): self
    {
        $this->element = Html::el($name);
        $this->elementName = $name;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Set element hidden
     * @param bool $hidden
     * @return BaseElement
     */
    public function setHidden(bool $hidden=true): self
    {
        $this->hidden = $hidden;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Set default class
     * @param string $defaultClass
     * @return BaseElement
     */
    public function setDefaultClass(string $defaultClass): self
    {
        $this->defaultClass = $defaultClass;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Set class
     * @param string $class
     * @return BaseElement
     */
    public function setClass(string $class): self
    {
        $this->class = $class;
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Add class (will not rewrite existing class)
     * @param string $class
     * @return BaseElement
     */
    public function addClass(string $class): self
    {
        $this->class = empty($this->class) ? $class : sprintf('%s %s', $this->class, $class);
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Remove class from class list
     * @param string $classToRemove
     * @return BaseElement
     */
    public function removeClass(string $classToRemove): self
    {
        $class = $this->element->class;
        $classList = explode(' ', $class);
        if(isset($classList[$classToRemove]))
            unset($classList[$classToRemove]);
        $this->element->class(implode(' ', $classList));
        return $this;
    }

    /**
     * Invalidate renderer
     * @return BaseElement
     */
    public function invalidateRenderer(): self
    {
        $this->needsRerender = true;
        return $this;
    }

    /**
     * Get element attributes
     * @return string
     */
    public function getAttributes(): string
    {
        if($this->needsRerender === true)
            $this->render();
        return $this->element->attributes();
    }

    /**
     * Is element hidden?
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Get element name
     * @return string|null
     */
    public function getElementName(): ?string
    {
        return $this->elementName;
    }

    /**
     * Set parent element
     * @param BaseElement|null $parent
     * @return BaseElement
     */
    public function setParent(?BaseElement $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     * @return BaseElement|null
     */
    public function getParent(): ?BaseElement
    {
        return $this->parent;
    }
}