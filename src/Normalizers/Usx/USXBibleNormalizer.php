<?php

namespace CodeZone\Bible\Normalizers\Usx;

use CodeZone\Bible\League\Fractal\TransformerAbstract;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsFilesetNormalizer;
use CodeZone\Bible\Normalizers\Normalizer;
use function CodeZone\Bible\container;

class USXBibleNormalizer implements Normalizer
{
    protected Normalizer $fileset_normalizer;
    protected Normalizer $book_normalizer;
    public function __construct(USXFilesetNormalizer $fileset_normalizer, USXBookNormalizer $books_normalizer )
    {
        $this->fileset_normalizer = $fileset_normalizer;
        $this->book_normalizer = $books_normalizer;
    }

    public function normalize( array $data ): array
    {
        return [
            'source'      => 'usx',
            'id'          => $data['id'],
            'copyright'   => $data['mark'],
            'name'        => $data['name'],
            'description' => $data['description'],
            "language"    => $data['language'],
            "iso"         => $data['iso'],
            'filesets'    => [
                $this->fileset_normalizer->normalize( $data )
            ],
            'books'       => array_map( fn( $data ) => $this->book_normalizer->normalize( $data ), $data['books'] )
        ];
    }
}
