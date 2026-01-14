<?php

namespace CodeZone\Bible\Sources\BibleBrains\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

/**
 * Class responsible for normalizing data structures related to Bible Brain's data, including filesets and books.
 * Implements the Normalizer interface and provides methods for processing and transforming input data.
 */
class BibleBrainsBibleNormalizer implements Normalizer
{
    protected Normalizer $fileset_normalizer;
    protected Normalizer $book_normalizer;

    public function __construct( BibleBrainsFilesetNormalizer $fileset_normalizer, BibleBrainsBookNormalizer $book_normalizer )
    {
        $this->fileset_normalizer = $fileset_normalizer;
        $this->book_normalizer = $book_normalizer;
    }

    /**
     * @param array $data
     * @return array
     */
    public function normalize( array $data ): array
    {
        return [
            'source'   => 'bible-brains',
            'id'       => $data['abbr'],
            'abbr'     => $data['abbr'],
            'code'     => $data['abbr'],
            'name'     => $data['name'],
            'filesets' => $this->includeFilesets( $data ),
            'books'    => $this->includeBooks( $data )
        ];
    }

    /**
     * Processes and normalizes a collection of filesets.
     *
     * @param array $data Input data containing a 'filesets' key with an array of filesets.
     * @return array An array of normalized filesets.
     */
    public function includeFilesets( $data ): array {
        $filesets = $data['filesets'] ?? [];

        if ( empty( $filesets ) ) {
            return [];
        }
        $merged = array_merge( ...array_values( $filesets ) );
        return array_map( fn( $data ) => $this->fileset_normalizer->normalize( $data ), $merged );
    }


    /**
     * Normalizes and includes books from the provided data.
     *
     * @param array $data An associative array containing book data under the 'books' key.
     * @return array An array of normalized books.
     */
    public function includeBooks( $data ): array {

        return array_map( fn( $data ) => $this->book_normalizer->normalize( $data ), $data['books'] ?? [] );
    }
}
