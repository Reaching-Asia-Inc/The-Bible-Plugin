<?php

namespace CodeZone\Bible\Sources\USX\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class USXFilesetNormalizer implements Normalizer
{
    public function normalize( array $bible ): array
    {
        return [
            'source' => 'usx',
            'id'     => $bible['id'],
            'type'   => 'text_plain',
            'size'   => 'C'
        ];
    }
}
