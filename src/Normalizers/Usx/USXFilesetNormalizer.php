<?php

namespace CodeZone\Bible\Normalizers\Usx;

use CodeZone\Bible\Normalizers\Normalizer;

class USXFilesetNormalizer implements Normalizer
{
    public function normalize( array $bible ): array
    {
        return [
            'source' => 'usx',
            'id'     => $bible['id'],
            'type'   => 'text_plain'
        ];
    }
}
