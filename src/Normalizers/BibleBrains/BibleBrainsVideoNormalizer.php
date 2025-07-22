<?php

namespace CodeZone\Bible\Normalizers\BibleBrains;

use CodeZone\Bible\Normalizers\Normalizer;

class BibleBrainsVideoNormalizer implements Normalizer
{

    public function normalize(array $data): array
    {
        return [
                'bandwidth' => (int) $data['BANDWIDTH'],
                'resolution' => $data['RESOLUTION'],
                'codecs' => trim( $data['CODECS'], '"' ),
                'url' => $data['URL']
            ];
    }
}
