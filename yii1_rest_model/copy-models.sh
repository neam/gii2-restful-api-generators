#!/bin/bash

script_path=`dirname $0`
cd $script_path/../../../../

set -o errexit;

DESTINATION="../../$1"

# only copy Base and Metadata models to application models directory
cp modules/yiirestapi/models/base/Base*.php $DESTINATION/base/

#console/yiic qa-state process --verbose

# only remove models that already exist
for file in modules/yiirestapi/models/*.php; do
    target="$DESTINATION/"$(basename "$file")
    if [ ! -e "$target" ]; then
        echo "New file $file available"
        mv $file $target
    else
        rm $file
    fi
done
