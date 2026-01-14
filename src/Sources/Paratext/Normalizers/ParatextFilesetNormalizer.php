<?php

namespace CodeZone\Bible\Sources\Paratext\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class ParatextFilesetNormalizer implements Normalizer
{
    public function normalize( array $bible ): array
    {
        return [
            'source' => 'paratext',
            'id'     => $bible['id'],
            'type'   => 'text_plain',
            'size'   => 'C'
        ];
    }
}
