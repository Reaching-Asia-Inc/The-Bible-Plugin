<?php

namespace CodeZone\Bible\Sources\USX\Normalizers;

use CodeZone\Bible\Sources\Normalizer;

class USXBookNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        return [
            'source'   => 'usx',
            'id'       => $data['code'],
            'name'     => $data['name'],
            'chapters' => array_keys( $data['chapters'] ),
            'testament' => $data['testament']
        ];
    }
}
