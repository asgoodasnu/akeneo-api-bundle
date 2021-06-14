<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;

class CategoryTreeBuilder
{
    /**
     * @param array<mixed> $items
     */
    public function build(string $rootCode, array $items): CategoryItem
    {
        $rootItem = $this->findItemByCode($rootCode, $items);

        $this->addChildren($rootItem, $items);

        return $rootItem;
    }

    /**
     * @param array<mixed> $items
     */
    private function addChildren(CategoryItem $item, array $items): void
    {
        $children = $this->findItemsByParent($item, $items);

        $item->setChildren($children);

        foreach ($children as $child) {
            $this->addChildren($child, $items);
        }
    }

    /**
     * @param array<mixed> $items
     *
     * @throws \InvalidArgumentException
     */
    private function findItemByCode(string $code, array $items): CategoryItem
    {
        foreach ($items as $item) {
            if ($item['code'] === $code) {
                return new CategoryItem($code, $item['labels']['de_DE'] ?? '');
            }
        }

        throw new \InvalidArgumentException("Item $code not found!");
    }

    /**
     * @param array<mixed> $items
     *
     * @return CategoryItem[]
     */
    private function findItemsByParent(CategoryItem $item, array $items): array
    {
        $children = [];

        foreach ($items as $i) {
            if ($i['parent'] === $item->getCode()) {
                $children[] = new CategoryItem($i['code'], $i['labels']['de_DE'] ?? '');
            }
        }

        return $children;
    }
}
