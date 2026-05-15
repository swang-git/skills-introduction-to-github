#!/usr/bin/env zsh
echo "==== starting install-yali-old-pics ====="
# Check if argument exists
if [[ $# -eq 0 ]]; then
  echo "Error: No argument provided"
  exit 1
fi

# Regex pattern for ANY valid number
# NUM_REGEX='^[+-]?([0-9]+(\.[0-9]*)?|\.[0-9]+)$'

# STRICT regex for CCYYMMDD (4-digit year, 01-12 month, 01-31 day)
CCYYMMDD_REGEX='^([0-9]{4})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$'

DATE_STR="$1"
# Validate first argument ($1)
if [[ $DATE_STR =~ $CCYYMMDD_REGEX ]]; then
#   echo "✅ '$DATE_STR' is a valid year month day"
else
  echo "❌ '$DATE_STR' is NOT a year month day"
  exit 1
fi

# Step 2: Mac BSD date validation (FIXED: detects fake dates)
PARSED=$(date -j -f "%Y%m%d" "$DATE_STR" +%Y%m%d 2>/dev/null)
if [[ "$PARSED" == "$DATE_STR" ]]; then
  echo "✅ VALID DATE: $DATE_STR"
else
  echo "❌ INVALID DATE: $DATE_STR (does not exist)"
  exit 1
fi

yapixdir="ya$DATE_STR"
echo "yapixdir=$yapixdir"
if [ ! -d $HOME/$yapixdir ]; then
	echo "\nNo pix directory $yapixdir"
	echo "Please: \nmkdir $yapixdir \nand put pix in folder $yapixdir\n"
	exit
fi

[[ $(ls -A $HOME/$yapixdir | wc -l) -gt 0 ]] || { echo "Empty directory $yapixdir, exit..."; exit 200; }
echo "time stamping files in $HOME/$yapixdir ..."
cd $HOME/$yapixdir
$HOME/bin/time-stamp-filename $DATE_STR
$HOME/bin/given-date-idx-filename $DATE_STR
$HOME/bin/reduce6-jpgsz
$HOME/bin/reduce2-jpgsz
echo "installing to website"
cp -p ssz/*.jpg $HOME/sites/webdata/pics/yali
cp -p thumbnails/*.jpg $HOME/sites/webdata/pics/yali/thumbnails
cd $HOME/sites/webdata/pics

echo "backup website yali -- tar and zip all file under webdata/pics/yali to $HOME/bak/ya"
tar cvzf $HOME/bak/ya/${DATE_STR}.tgz yali

echo "move all processed files to $HOME/YA-BAK"
cd $HOME
mv $yapixdir YA-BAK
exit



