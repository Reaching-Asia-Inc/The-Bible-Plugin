<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;

class FileSets {
	/**
	 * Returns the group based on the given type.
	 *
	 * @param array $bible The array containing the bible data.
	 * @param string $type The type to check.
	 *
	 * @return string The group name. Returns "dpb-vid" if the type contains the word "video", otherwise returns "dpb-prod".
	 */
	public function group_from_type( array $bible, string $type ): string {
        return str_contains($type, 'video') ? "dbp-vid" : "dbp-prod";
    }

	/**
	 * Plucks the first matching fileset based on the given conditions.
	 *
	 * @param array $bible The array containing the bible data.
	 * @param array $book The array containing the book data.
	 * @param array $fileset_types The array containing the fileset types to search for.
	 */
	public function pluck( array $bible, array $book, array $fileset_types ) {
		foreach ( $fileset_types as $fileset_type ) {
			$fileset = $this->resolve( $bible, $book, $fileset_type );
			if ( $fileset ) {
				return $fileset;
			}
		}

		return null;
	}

	/**
	 * Resolves the fileset for the given bible, book, and fileset type.
	 *
	 * @param array $bible The array containing the bible data.
	 * @param array $book The array containing the book data.
	 * @param string $fileset_type The type of fileset to resolve.
	 *
	 * @return array|null The resolved fileset. Returns null if no fileset is found.
	 */
	public function resolve( array $bible, array $book, string $fileset_type ) {
		$fileset_group = $this->group_from_type( $bible, $fileset_type );
		$filesets      = $bible['filesets'][ $fileset_group ] ?? [];

        foreach ($filesets as $fileset) {
            if ($fileset['type'] === $fileset_type
                && ($fileset["size"] === "C" || str_contains($fileset["size"], $book["testament"]))) {
                return $fileset;
            }
        }
        return null;
	}
}
