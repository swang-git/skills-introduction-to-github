#!/bin/bash
echo "==== starting install-yali-pics ====="
yatoday="ya"`date +%Y%m%d`
###echo "yatoday=$yatoday"
[[ -d $HOME/$yatoday ]] || { echo "No directory $yatoday exiting ..."; exit 100; }
# # # [[ ! -z "$(ls -A $HOME/$yatoday)" ]] || { echo "Empty directory $yatoday exit..."; exit 101; }
# # # [[ -z "$(find $HOME/$yatoday -maxdepth 0 -empty)" ]] || { echo "Empty directory $yatoday exit..."; exit 102; }
[[ $(ls -A $HOME/$yatoday | wc -l) -gt 0 ]] || { echo "Empty directory $yatoday, exit..."; exit 200; }
echo "date indexing files in $HOME/$yatoday ..."
cd $HOME/$yatoday
$HOME/bin/dat-idx-filename
$HOME/bin/reduce6-jpgsz
$HOME/bin/reduce2-jpgsz
cp -p ssz/*.jpg $HOME/sites/webdata/pics/yali
cp -p thumbnails/*.jpg $HOME/sites/webdata/pics/yali/thumbnails
cd $HOME/sites/webdata/pics
tar cvzf $HOME/bak/ya/`date +%Y%m%d`.tar.gz yali
cd $HOME
mv $yatoday YA-BAK
exit



