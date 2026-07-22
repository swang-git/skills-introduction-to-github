for f in *.JPG *.jpeg; do mv "$f" "${f%.*}.jpg"; done
