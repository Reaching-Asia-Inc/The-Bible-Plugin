<?php

namespace CodeZone\Bible\Normalizers;

interface Normalizer
{
    public function normalize( array $data ): array;
}
