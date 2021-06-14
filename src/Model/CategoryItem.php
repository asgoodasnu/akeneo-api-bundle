<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Model;

class CategoryItem
{
    private string $code;
    private string $title;

    /** @var CategoryItem[] */
    private array $children = [];

    public function __construct(string $code, string $title)
    {
        $this->code = $code;
        $this->title = $title;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return CategoryItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param CategoryItem[] $children
     */
    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }
}
