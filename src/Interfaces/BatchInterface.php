<?php

namespace App\Interfaces;

use Doctrine\Common\Collections\Collection;

/**
 * An interface to easily handle a subset of database items.
 * 
 * @package App\Interface
 * 
 * @method Collection getItems()
 * @method int        getSize()
 * @method int        getTotalCount()
 * @method bool       hasNextItems()
 */
interface BatchInterface
{
    /**
     * Get the items in the current batch.
     * 
     * @return Collection 
     */
    public function getItems(): Collection;

    /**
     * Get the number of items in the current batch.
     * 
     * @return int 
     */
    public function getSize(): int;

    /**
     * Get the total number of items in the database.
     * 
     * @return int 
     */
    public function getTotalCount(): int;

    /**
     * Get whether previous items can be fetched from the database or not.
     * 
     * @return bool 
     */
    public function hasPreviousItems(): bool;

    /**
     * Get whether additional items can be fetched from the database or not.
     * 
     * @return bool 
     */
    public function hasNextItems(): bool;

    /**
     * Get the current page number for the batch.
     * 
     * @return int 
     */
    public function getPageNumber(): int;

    /**
     * Get the page number for the previous batch
     * or false if there is no previous page (i.e. the current page is the first one).
     * 
     * @return int|false 
     */
    public function getPreviousPageNumber(): int|false;

    /**
     * Get the page number for the next batch
     * or false if there is no next page (i.e. the current page is the last one).
     * 
     * @return int|false 
     */
    public function getNextPageNumber(): int|false;
}
