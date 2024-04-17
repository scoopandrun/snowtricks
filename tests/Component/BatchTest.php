<?php

namespace App\Tests\Component;

use App\Component\Batch;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class BatchTest extends TestCase
{
    public function testGetItems()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items);

        $this->assertInstanceOf(ArrayCollection::class, $batch->getItems());
        $this->assertEquals($items, $batch->getItems()->toArray());
    }

    public function testGetSize()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items);

        $this->assertEquals(count($items), $batch->getSize());
    }

    public function testGetTotalCount()
    {
        $items = ['item1', 'item2', 'item3'];
        $totalCount = 10;
        $batch = new Batch($items, 1, 1, null, $totalCount);

        $this->assertEquals($totalCount, $batch->getTotalCount());
    }

    public function testHasPreviousItems()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items, 2, 4);

        $this->assertTrue($batch->hasPreviousItems());

        $batch = new Batch($items, 1, 1);

        $this->assertFalse($batch->hasPreviousItems());
    }

    public function testHasNextItems()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items, 1, totalCount: 10);

        $this->assertTrue($batch->hasNextItems());

        $batch = new Batch($items, 1, totalCount: 3);

        $this->assertFalse($batch->hasNextItems());
    }

    public function testGetPageNumber()
    {
        $items = ['item1', 'item2', 'item3'];
        $pageNumber = 2;
        $batch = new Batch($items, $pageNumber);

        $this->assertEquals($pageNumber, $batch->getPageNumber());
    }

    public function testGetPreviousPageNumber()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items, 2, 4);

        $this->assertEquals(1, $batch->getPreviousPageNumber());

        $batch = new Batch($items, 1);

        $this->assertFalse($batch->getPreviousPageNumber());
    }

    public function testGetNextPageNumber()
    {
        $items = ['item1', 'item2', 'item3'];
        $batch = new Batch($items, 1, totalCount: 10);

        $this->assertEquals(2, $batch->getNextPageNumber());

        $batch = new Batch($items, 1, totalCount: 3);

        $this->assertFalse($batch->getNextPageNumber());
    }
}
