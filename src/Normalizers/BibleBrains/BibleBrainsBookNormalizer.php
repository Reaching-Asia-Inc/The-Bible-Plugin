<?php

namespace CodeZone\Bible\Normalizers\BibleBrains;

use CodeZone\Bible\Normalizers\Normalizer;

class BibleBrainsBookNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source'   => 'bible-brains',
            'id'       => $data['book_id'],
            'name'     => $data['name'],
            'chapters' => $data['chapters'],
            'testament' => $data['testament']
        ];
    }
}
