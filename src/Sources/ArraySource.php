<?php

namespace CodeZone\Bible\Sources;

abstract class ArraySource
{
    /**
     * Retrieve all items from the collection.
     *
     * @return array List of all items.
     */
    abstract public function all(): array;

    /**
     * Search for Bible by name using fuzzy matching.
     *
     * @param string $query The search query.
     * @param int $similarity Minimum similarity threshold (0-100).
     * @return array List of matching Bible.
     */
    public function search( string $query, int $similarity = 60 ): array
    {
        $bibles = $this->all();
        $results = [];

        foreach ( $bibles as $key => $bible ) {
            similar_text( strtolower( $query ), strtolower( $bible['name'] ), $percent );
            if ( $percent >= $similarity ) {
                $results[$key] = $bible;
            }
        }

        return $results;
    }

    /**
     * Find multiple records by their IDs.
     *
     * @param array $keys Array of record IDs
     * @return array    Array of found records
     */
    public function find_many( array $keys ): array
    {
        $bibles = $this->all();
        $results = [];

        foreach ( $bibles as $key => $bible ) {
            if ( in_array( $key, $keys ) ) {
                $results[$key] = $bible;
            }
        }

        return $results;
    }

    /**
     * Find a specific record by key.
     *
     * @param string $key The key to search for in the records
     * @return mixed      The record associated with the key, or null if not found
     */
    public function find( string $key )
    {
        $bibles = $this->all();
        return $bibles[$key] ?? null;
    }

    /**
     * Get Bible by language code.
     *
     * @param string $name The language code to filter by.
     * @return array List of Bible in the specified language.
     */
    public function where( string $name, $value ): array
    {
        $bibles = $this->all();
        $results = [];

        foreach ( $bibles as $key => $bible ) {
            if ( isset( $bible[$name] ) && $bible[$name] === $value ) {
                $results[$key] = $bible;
            }
        }

        return $results;
    }
}
