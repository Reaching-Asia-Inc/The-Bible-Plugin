<?php

namespace CodeZone\Bible\Resources;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Sources\Aggregators\BookAggregator;

/**
 * Book service class for interacting with books.
 */
class Book
{

    private $aggregator;

    /**
     * Constructor method to initialize the BookAggregator.
     *
     * @param BookAggregator $aggregator The aggregator instance for managing books.
     *
     * @return void
     */
    public function __construct(
        BookAggregator $aggregator
    ) {
        $this->aggregator = $aggregator;
    }


    /**
     * Retrieves all books from the specified bible version.
     *
     * @param string|array $bible The version of the bible to retrieve the books from.
     *                            Can be either the version ID or an associative array containing the books.
     *                            Defaults to 'ENGESV'.
     *
     * @return array The array containing all the books from the specified bible version.
     * @throws BiblePluginException
     */
    public function all( $bible = 'ENGESV' ): array
    {
        return $this->aggregator->all( $bible );
    }

    /**
     * Find a book within the given Bible.
     *
     * @param string $data The book to find.
     * @param string|array $bible The Bible to search in. Defaults to 'ENGESV'.
     *
     * @return ?array Returns an array representing the found book, or null if not found.
     * @throws BiblePluginException
     */
    public function find( string $book, string $bible = 'ENGESV' ): ?array
    {
        return $this->aggregator->find( $book, $bible );
    }
}
