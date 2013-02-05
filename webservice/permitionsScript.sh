#!/bin/sh
#Script for fixing file permitions in debian environments.

DIR=$(pwd)
APACHEGROUP="www-data"

echo "#############################"
echo "Fixing group of $DIR to www-data:" 
echo " * chgrp -R "$APACHEGROUP" "$DIR""
chgrp -R www-data "$DIR"
echo "Fixing file permitions of $DIR to 775:"
echo " * chmod -R 775 "$DIR""
chmod -R 775 "$DIR"
echo "#########SCRIPT#DONE!########"
