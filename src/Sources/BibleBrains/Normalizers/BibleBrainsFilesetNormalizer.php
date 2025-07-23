<?php

namespace CodeZone\Bible\Sources\BibleBrains\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

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
