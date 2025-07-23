<?php

namespace CodeZone\Bible\Sources\USX\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class USXLanguageNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source' => 'usx',
            'id' => $data['iso'],
            'code' => $data['iso'],
            'name' => $data['language'],
            'autonym' => $data['language'],
        ];
    }
}
