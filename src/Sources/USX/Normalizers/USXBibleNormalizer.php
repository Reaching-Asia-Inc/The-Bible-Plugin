<?php

namespace CodeZone\Bible\Sources\USX\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class USXBibleNormalizer implements Normalizer
{
    protected Normalizer $fileset_normalizer;
    protected Normalizer $book_normalizer;
    public function __construct( USXFilesetNormalizer $fileset_normalizer, USXBookNormalizer $books_normalizer )
    {
        $this->fileset_normalizer = $fileset_normalizer;
        $this->book_normalizer = $books_normalizer;
    }

    public function normalize( array $data ): array
    {
        return [
            'source'      => 'usx',
            'id'       => $data['id'],
            'code'     => $data['id'],
            'name'     => $data['name'],
            'filesets' => [
                $this->fileset_normalizer->normalize( $data )
            ],
            'books'    => array_map( fn( $data ) => $this->book_normalizer->normalize( $data ), $data['books'] ?? [] )
        ];
    }
}
