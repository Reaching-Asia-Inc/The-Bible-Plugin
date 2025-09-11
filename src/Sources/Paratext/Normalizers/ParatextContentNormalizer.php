<?php

namespace CodeZone\Bible\Sources\Paratext\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class ParatextContentNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source'       => 'paratext',
            'book_id'      => $data['book_id'],
            'book_name'    => $data['book_name'],
            'chapter'      => $data['chapter'],
            'verse_start'  => $data['verse_start'],
            'verse_end'    => $data['verse_end'],
            'verse_text'   => $data['verse_text'],
            'path'         => null,
            'thumbnail'    => null,
            'filesize'     => null,
            'chater_start' => null,
            'chater_end'   => null,
        ];
    }
}
