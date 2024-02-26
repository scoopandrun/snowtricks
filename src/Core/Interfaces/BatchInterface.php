<?php

namespace App\Core\Interfaces;

use Doctrine\Common\Collections\Collection;

/**
 * An interface to easily handle a subset of database items.
 * 
 * @package App\Core\Interface
 * 
 * @method Collection getItems()
 * @method int        getBatchSize()
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
    public function getBatchSize(): int;

    /**
     * Get the total number of items in the database.
     * 
     * @return int 
     */
    public function getTotalCount(): int;

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
     * Get the page number for the next batch
     * or false if there is no next page (i.e. the current page is the last one).
     * 
     * @return int|false 
     */
    public function getNextPageNumber(): int|false;
}
