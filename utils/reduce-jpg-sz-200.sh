#!/bin/bash

mkdir thumbnails

for img in *.jpg; do
    magick "$img" -auto-orient -thumbnail 200x200 -quality 50 "thumbnails/${img}"

    # Copy timestamp from original file to ssz
    touch -r "$img" "thumbnails/$img"
done

