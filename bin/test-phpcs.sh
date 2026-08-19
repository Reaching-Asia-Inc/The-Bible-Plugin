set -e

cd "$(dirname "${BASH_SOURCE[0]}")/../"

# Re-register phpcs coding standard paths (e.g. WordPress) directly, without
# going through Composer - needed since vendor/ may come from a build artifact
# rather than a fresh `composer install`/`require` in this environment, so the
# dealerdirect installer plugin's hook may not have run against it.
vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility,vendor/phpcsstandards/phpcsextra,vendor/phpcsstandards/phpcsutils,vendor/wp-coding-standards/wpcs

if [ "$(php -r 'echo version_compare( phpversion(), "7.0", ">=" ) ? 1 : 0;')" != 1 ] ; then
    vendor/bin/phpcs bible-plugin.php
    exit
fi

eval vendor/bin/phpcs
