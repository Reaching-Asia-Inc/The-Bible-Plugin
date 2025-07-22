<?php

namespace CodeZone\Bible\Normalizers\BibleBrains;

use CodeZone\Bible\Normalizers\Normalizer;

class BibleBrainsFilesetNormalizer implements Normalizer
{
    public function normalize( array $data ): array
    {
        return [
            'source' => 'bible-brains',
            'id'     => $data['id'],
            'type'   => $data['type'],
            'size'   => $data['size']
        ];
    }
}
