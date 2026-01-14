<?php

namespace CodeZone\Bible\Sources\Paratext\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class ParatextLanguageNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source' => 'paratext',
            'id' => $data['iso'],
            'code' => $data['iso'],
            'name' => $data['language'],
            'autonym' => $data['language'],
        ];
    }
}
