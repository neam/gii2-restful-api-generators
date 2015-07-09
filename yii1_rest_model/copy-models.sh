#!/bin/bash

script_path=`dirname $0`
cd $script_path/../../../../

set -o errexit;

DESTINATION="../../$1"

# only copy Base and Metadata models to application models directory
cp modules/yiirestapi/models/base/Base*.php $DESTINATION/base/
rm modules/yiirestapi/models/base/Base*.php

#console/yiic qa-state process --verbose

# only remove models that already exist
for file in modules/yiirestapi/models/*.php; do
    target="$DESTINATION/"$(basename "$file")
    if [ ! -e "$target" ]; then
        echo "New file $target available"
        mv $file $target
    else
        rm $file
        #echo "File $file already exists and was not overwritten"
        :
    fi
done
