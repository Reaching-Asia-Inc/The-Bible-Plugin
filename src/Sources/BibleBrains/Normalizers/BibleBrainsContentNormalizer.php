<?php

namespace CodeZone\Bible\Sources\BibleBrains\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class BibleBrainsContentNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
       return [
           'source'       => 'bible-brains',
           'book_id'      => $data['book_id'],
           'chapter'      => $data['chapter'] ?? null,
           'chater_start' => $data['chater_start'] ?? null,
           'chater_end'   => $data['chater_end'] ?? null,
           'verse_start'  => $data['verse_start'],
           'verse_end'    => $data['verse_end'],
           'path'         => $data['path'] ?? null,
           'thumbnail'    => $data['thumbnail'] ?? null,
           'filesize'     => $data['filesize_in_bites'] ?? null,
           'verse_text'   => $data['verse_text'] ?? null,
       ];
    }
}
