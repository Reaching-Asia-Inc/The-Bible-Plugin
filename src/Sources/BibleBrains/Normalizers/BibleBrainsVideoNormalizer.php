<?php

namespace CodeZone\Bible\Sources\BibleBrains\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class BibleBrainsVideoNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
                'source' => 'bible-brains',
                'bandwidth' => (int) $data['BANDWIDTH'],
                'resolution' => $data['RESOLUTION'],
                'codecs' => trim( $data['CODECS'], '"' ),
                'url' => $data['URL']
            ];
    }
}
