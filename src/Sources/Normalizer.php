<?php

namespace CodeZone\Bible\Sources;

/**
 * Provides an interface for normalizing data across sources.
 */
interface Normalizer
{
    /**
     * Normalizes the input data array.
     *
     * @param array $data The input array to be normalized.
     * @return array The normalized array.
     */
    public function normalize( array $data ): array;
}
