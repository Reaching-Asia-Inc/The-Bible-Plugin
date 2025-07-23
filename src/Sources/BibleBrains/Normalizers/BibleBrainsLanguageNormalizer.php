<?php

namespace CodeZone\Bible\Sources\BibleBrains\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class BibleBrainsLanguageNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
       return [
           'source' => 'bible-brains',
           'id' => $data['id'],
           'code' => $data['code'] ?? $data['iso'],
           'name' => $data['name'],
           'autonym' => $data['autonym'],
       ];
    }
}
