<?php

namespace CodeZone\Bible\Services;

class FileSets {
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
		$filesets      = $bible['filesets'];

        foreach ( $filesets as $fileset ) {
            if ( $fileset['type'] === $fileset_type
                && ( $fileset["size"] === "C" || str_contains( $fileset["size"], $book["testament"] ) ) ) {
                return $fileset;
            }
        }
        return null;
	}
}
