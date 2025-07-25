<?php

namespace CodeZone\Bible\Sources;

abstract class Aggregator
{
    protected Normalizer $bb_normalizer;
    protected Normalizer $usx_normalizer;

    const COMPARE_KEY = "code";

    /**
     * Aggregates and merges two arrays of data by normalizing their structure and removing duplicates
     * based on a specified key.
     *
     * @param array $usx_data An array of data to be normalized and used as the base for aggregation.
     * @param array $bb_data An array of data to be normalized and filtered before merging.
     * @param ?string $compare The key used to compare and remove duplicates between the two arrays. Default is 'id'.
     * @return array The aggregated and merged array containing unique entries from both input arrays.
     */
    public function aggregate( array $usx_data, array $bb_data, string $compare = null ): array
    {
        $usx_data = $this->normalize_usx_items( $usx_data );
        $bb_data = $this->normalize_bb_items( $bb_data );
        return $this->merge( $bb_data, $usx_data, $compare );
    }

    public function merge( array $secondary, array $primary, string $compare = null ): array
    {
        $compare = $compare ?? static::COMPARE_KEY;

        $primary_ids = array_map(function ( $id ) {
            if ( is_string( $id ) ) {
                return strtolower( $id );
            }
            return $id;
        }, array_column( $primary, $compare ));

        $secondary = array_filter( $secondary, function ( $bible ) use ( $compare, $primary_ids ) {
            if ( empty( $bible[$compare] ) ) {
                return false;
            }
            $value = $bible[$compare];
            if ( is_string( $value ) ) {
                $value = strtolower( $value );
            }
            return !in_array( $value, $primary_ids );
        });

        return array_merge( array_values( $secondary ), array_values( $primary ) );
    }

    /**
     * Aggregates two items by normalizing them using the appropriate normalizer and selecting the result.
     *
     * @param mixed $usx_item The USX item to be normalized. Can be null.
     * @param mixed $bb_item The BB item to be normalized. Can be null.
     * @return array|null The result of choosing between the normalized items.
     */
    public function aggregate_item( $usx_item, $bb_item ): ?array
    {
        return $this->choose(
            $usx_item ? $this->normalize_usx( $usx_item ) : null,
            $bb_item ? $this->normalize_bb( $bb_item ) : null
        );
    }

    /**
     * Chooses between two Bible representations and returns the appropriate one.
     *
     * @param array|null $usx_bible The USX Bible representation. Can be null.
     * @param array|null $bb_bible The BB Bible representation. Can be null.
     * @return array|null The chosen Bible representation, preferring $usx_bible if it is available.
     */
    public function choose( ?array $usx_bible, ?array $bb_bible ): ?array
    {
        if ( $usx_bible ) {
            return $usx_bible;
        }

        return $bb_bible;
    }

    /**
     * Normalizes an array of USX items.
     *
     * @param array $usx_data Array of USX items to normalize
     * @return array Array of normalized USX items
     */
    protected function normalize_usx_items( array $usx_data ): array
    {
        return array_map( fn( $data ) => $this->normalize_usx( $data ), $usx_data );
    }

    /**
     * Normalizes an array of BB items.
     *
     * @param array $bb_data Array of BB items to normalize
     * @return array Array of normalized BB items
     */
    protected function normalize_bb_items( array $bb_data ): array
    {
        return array_map( fn( $data ) => $this->normalize_bb( $data ), $bb_data );
    }

    /**
     * Normalizes a single USX item.
     *
     * @param array $usx_item USX item to normalize
     * @return array Normalized USX item
     */
    protected function normalize_usx( array $usx_item ): array
    {
        return $this->usx_normalizer->normalize( $usx_item );
    }

    /**
     * Normalizes a single BB item.
     *
     * @param array $bb_item BB item to normalize
     * @return array Normalized BB item
     */
    protected function normalize_bb( array $bb_item ): array
    {
        return $this->bb_normalizer->normalize( $bb_item );
    }
}
