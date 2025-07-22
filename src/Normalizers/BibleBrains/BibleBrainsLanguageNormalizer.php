<?php

namespace CodeZone\Bible\Normalizers\BibleBrains;

use CodeZone\Bible\Normalizers\Normalizer;

class BibleBrainsLanguageNormalizer implements Normalizer
{

    public function normalize(array $data): array
    {
       return [
           'id' => $data['id'],
           'code' => $data['code'] ?? $data['iso'],
           'name' => $data['name'],
           'autonym' => $data['autonym'],
       ];
    }
}
