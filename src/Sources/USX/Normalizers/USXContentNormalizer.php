<?php

namespace CodeZone\Bible\Sources\USX\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class USXContentNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source'       => 'usx',
            'book_id'      => $data['book_id'],
            'book_name'    => $data['book_name'],
            'chapter'      => $data['chapter'],
            'verse_start'  => $data['verse_start'],
            'verse_end'    => $data['verse_end'],
            'verse_text'   => $data['verse_text'],
            'path'         => null,
            'thumbnail'    => null,
            'filesize'     => null,
            'chapter_start' => null,
            'chapter_end'   => null,
        ];
    }
}
