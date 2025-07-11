if [ "$(php -r 'echo version_compare( phpversion(), "7.0", ">=" ) ? 1 : 0;')" != 1 ] ; then
    php -l ../bible-plugin.php
    exit
fi

found_error=0

while read -d '' filename ; do

    # php -l checks the file for syntax errors
    php -l "$filename" || found_error=1

done < <(find . d \( -path ./vendor -o -path ./vendor-scoped \) -prune -o -name "*.php" -print0)

exit $found_error
