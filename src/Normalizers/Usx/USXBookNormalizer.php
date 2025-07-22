<?php

namespace CodeZone\Bible\Normalizers\Usx;

use CodeZone\Bible\League\Fractal\TransformerAbstract;
use CodeZone\Bible\Normalizers\Normalizer;

class USXBookNormalizer implements Normalizer
{

    public function normalize( array $data ): array
    {
        dd( $data );
    }
}
