#!/bin/bash
mkdir ssz

for img in *.jpg; do
    magick "$img" -auto-orient -thumbnail 600x600 -quality 60 "ssz/${img}"

    # Copy timestamp from original file to ssz
    touch -r "$img" "ssz/$img"
done

