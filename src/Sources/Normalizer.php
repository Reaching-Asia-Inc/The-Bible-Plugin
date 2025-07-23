<?php

namespace CodeZone\Bible\Sources;

interface Normalizer
{
    public function normalize( array $data ): array;
}
