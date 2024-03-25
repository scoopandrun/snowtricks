<?php

namespace App\Component;

use App\Interfaces\BatchInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Batch implements BatchInterface
{
    protected Collection $items;
    protected int $firstItemIndex = 0;
    protected int $lastItemIndex = 0;
    protected int $totalCount = 0;
    protected int $pageNumber = 1;

    /**
     * @param array    $items      Array of items.
     * @param int      $firstIndex Index of the first item of the batch (1-based).
     * @param null|int $lastIndex  Index of the last item of the batch (1-based).
     * @param null|int $totalCount Total number of items in the database.
     */
    public function __construct(
        array $items = [],
        int $pageNumber = 1,
        int $firstIndex = 1,
        ?int $lastIndex = null,
        ?int $totalCount = null,
    ) {
        $this->items = new ArrayCollection($items);

        $this->pageNumber = $pageNumber;

        $this->firstItemIndex = $firstIndex;

        $this->lastItemIndex = $lastIndex ?: $firstIndex + count($items) - 1;

        $this->totalCount = $totalCount ?: count($items);
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getSize(): int
    {
        return $this->items->count();
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function hasPreviousItems(): bool
    {
        return $this->firstItemIndex > 1;
    }

    public function hasNextItems(): bool
    {
        return $this->lastItemIndex < $this->totalCount;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getPreviousPageNumber(): int|false
    {
        return $this->hasPreviousItems() ? $this->pageNumber - 1 : false;
    }

    public function getNextPageNumber(): int|false
    {
        return $this->hasNextItems() ? $this->pageNumber + 1 : false;
    }
}
