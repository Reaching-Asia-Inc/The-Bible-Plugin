<?php

namespace CodeZone\Bible\Normalizers\Usx;

use CodeZone\Bible\Normalizers\Normalizer;

class USXContentNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source' => 'usx',
            'book_id' => $data['book_id'],
            'chapter' => $data['chapter'],
            'verse_start' => $data['verse_start'],
            'verse_end' => $data['verse_end'],
            'content' => $data['passage']
        ];
    }
}
