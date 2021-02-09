<?php

namespace Asgoodasnew\AkeneoApiBundle\Tests\Model;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use PHPUnit\Framework\TestCase;

class CategoryItemTest extends TestCase
{
    public function testGetChildren(): void
    {
        $children = [
            new CategoryItem('child', 'title child'),
        ];

        $item = new CategoryItem('code', 'title');
        $item->setChildren($children);

        self::assertSame($children, $item->getChildren());
    }

    public function testGetTitle(): void
    {
        $item = new CategoryItem('code', 'title');

        self::assertSame('title', $item->getTitle());
    }
}
