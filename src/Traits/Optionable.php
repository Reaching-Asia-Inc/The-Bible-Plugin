<?php

namespace CodeZone\Bible\Traits;

trait Optionable
{
    /**
     * Transforms an array of records into a format suitable for use as options.
     *
     * @param array $records List of records to transform
     * @return array          Transformed array of options
     */
    public function as_options( array $records ): array {
        // Deduplicate records based on their 'id' value
        $unique_records = [];
        foreach ( $records as $record ) {
            $id = $record['id'] ?? $record['code'] ?? null;
            if ( !empty( $id ) && !isset( $unique_records[$id] ) ) {
                $unique_records[$id] = $record;
            }
        }

        return array_values(array_filter(
            array_map( function ( $option ) {
                return $this->map_option( $option );
            }, $unique_records ),
            fn( $option ) => !empty( $option['value'] ) && !empty( $option['itemText'] )
        ));
    }

    /**
     * Map a single record to an option format.
     *
     * @param array $record Record to transform
     * @return array       Option with value and itemText
     */
    public function map_option( array $record ): array {
        return [
            'value'    => (string) ( $record['id'] ?? $record['code'] ?? '' ),
            'itemText' => (string) ( $record['name'] ?? '' ),
        ];
    }
}
