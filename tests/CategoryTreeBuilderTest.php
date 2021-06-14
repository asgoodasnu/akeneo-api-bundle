<?php

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\CategoryTreeBuilder;
use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use PHPUnit\Framework\TestCase;

class CategoryTreeBuilderTest extends TestCase
{
    private CategoryTreeBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new CategoryTreeBuilder();
    }

    public function testBuildRootNotFound(): void
    {
        $items = [];

        self::expectException(\InvalidArgumentException::class);

        $this->builder->build('root', $items);
    }

    public function testBuild(): void
    {
        $items = [
            [
                'code' => 'root',
                'parent' => null,
                'labels' => [
                    'de_DE' => 'Root',
                ],
            ],
            [
                'code' => 'cat1',
                'parent' => 'root',
                'labels' => [
                    'de_DE' => 'Cat1',
                ],
            ],
            [
                'code' => 'cat2',
                'parent' => 'cat1',
                'labels' => [
                    'de_DE' => 'Cat2',
                ],
            ],
        ];

        $expectedItem = (new CategoryItem('root', 'Root'))
            ->setChildren([
                (new CategoryItem('cat1', 'Cat1'))
                    ->setChildren([
                        new CategoryItem('cat2', 'Cat2'),
                    ]),
            ]);

        self::assertEquals(
            $expectedItem,
            $this->builder->build('root', $items)
        );
    }
}
