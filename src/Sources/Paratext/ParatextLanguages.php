<?php

namespace CodeZone\Bible\Sources\Paratext;

class ParatextLanguages
{
    private ParatextBibles $local_bibles;

    public function __construct( ParatextBibles $local_bibles )
    {
        $this->local_bibles = $local_bibles;
    }

    public function all() {
        $bibles = $this->local_bibles->all();
        $languages = [];
        foreach ( $bibles as $bible ) {
            $key = $bible['iso'] ?? '';
            if ( !empty( $key ) && !isset( $languages[$key] ) ) {
                $languages[$key] = [
                    'language' => $bible['language'] ?? '',
                    'iso' => $bible['iso'] ?? ''
                ];
            }
        }
        return array_values( $languages );
    }

    public function find( string $iso ): ?array
    {
        $languages = $this->all();
        foreach ( $languages as $language ) {
            if ( $language['iso'] === $iso ) {
                return $language;
            }
        }
        return null;
    }

    public function search( string $search ): array
    {
        $languages = $this->all();
        $response = [];
        foreach ( $languages as $language ) {
            if ( stripos( $language['language'], $search ) !== false
            || stripos( $language['iso'], $search ) !== false ) {
                $response[] = $language;
            }
        }
        return $response;
    }
}
