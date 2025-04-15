#!/bin/bash

# today=`date '+%Y%m%d'`
today=`date '+%a'`
tar_dir=/dtv/BAK/pj
exts='-name "*.php" -or -name "*.sh" -or -name "*.html" -or -name "*.vue" -or -name "*.js" -or -name "*.json"'

cd /sites/projects

for dir in */; do
    proj=${dir::-1}
    tar_file=$tar_dir/${today}_$proj.tgz
    exclude_opt="--exclude=node_modules --exclude=__pycache__"
    if [[ $proj =~ ^init_quasar ]]
        then exclude_opt=''
    fi
    tarcmd="tar czfv $tar_file $exclude_opt $proj"
    echo $tarcmd
    $tarcmd
done

exit

# proj_dir=projectd/arts

# if [ ! d $tag_dir ]; then
#     mkdir -p $tag_dir
# fi


# echo tar --exclude='./node_modules' -czf $tar_file .

# cd /home/swang/projects/arts

# tar --exclude='./node_modules' -cf $tar_file .
