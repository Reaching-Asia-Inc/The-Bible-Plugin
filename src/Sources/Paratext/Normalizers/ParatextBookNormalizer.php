<?php

namespace CodeZone\Bible\Sources\Paratext\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class ParatextBookNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source'   => 'paratext',
            'id'       => $data['code'],
            'name'     => $data['name'],
            'chapters' => array_keys( $data['chapters'] ),
            'testament' => $data['testament']
        ];
    }
}
