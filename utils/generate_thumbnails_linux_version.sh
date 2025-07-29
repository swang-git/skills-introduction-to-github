#!/bin/bash

# Check if directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <directory> (/sites/webdata/pics/yali) [thumbnail_width]"
    exit 1
fi

# Set thumbnail width (default: 300px)
THUMBNAIL_WIDTH="${2:-200}"

# Navigate to the target directory
cd "$1" || exit

# Create thumbnails directory if it doesn't exist
THUMB_DIR="thumbnails"
mkdir -p "$THUMB_DIR"

SRC_DIR="."

# Supported image formats (add more if needed)
IMAGE_EXTS=("jpg" "jpeg" "png" "gif" "webp")
# Supported video formats
VIDEO_EXTS=("mp4" "mkv" "avi" "mov")

# Generate thumbnails for images
for ext in "${IMAGE_EXTS[@]}"; do
    for img in "$SRC_DIR"/*."$ext"; do
        if [ -f "$img" ]; then
            thumb="$THUMB_DIR/$(basename "$img")"
            # echo "checking thumb=$thumb"
            # Skip if already a thumbnail and file size > 0
            filesize=$(stat -c %s "$thumb" 2>/dev/null)
            # echo "file=[$thumb]"
            # echo "filesz=[$filesize]"

            if [ -n "$filesize" ] && [ "$filesize" -gt 0 ]; then
                echo "thumbnail exists and is not empty, do next...(size: $filesize bytes)"
                continue
            fi
            # if [ -f "$thumb" ]; then
            #     echo "thumbnail exist:[$thumb] process next ..."
            #     continue
            # fi
            # [ -s "$thumb" ] || { echo "thumbnail exist:[$thumb] process next ..."; continue; }

            # Resize image (adjust dimensions as needed)
            # convert "$img" -resize 200x200 "$thumb"
            # convert "$img" -resize $THUMBNAIL_WIDTH "$thumb"
            echo "processing img=$img"
            echo "processing thumb=$thumb"
            magick "$img" -resize $THUMBNAIL_WIDTH "$thumb"
            # Copy original timestamps (modification/access time)
            touch -r "$img" "$thumb"
            # Copy ownership/permissions (requires sudo if owned by another user)
            chmod --reference="$img" "$thumb"
            # chown --reference="$img" "$thumb"
        fi
    done
done

# Generate thumbnails for videos (first frame as thumbnail)
for ext in "${VIDEO_EXTS[@]}"; do
    for vid in "$SRC_DIR"/*."$ext"; do
        if [ -f "$vid" ]; then
            thumb="$THUMB_DIR/$(basename "${vid%.*}").${ext}"  # Keep original extension
            # Extract first frame (adjust -ss for time offset)
            ffmpeg -i "$vid" -ss 00:00:01 -vframes 1 "$thumb" -y
            # Copy metadata from original
            touch -r "$vid" "$thumb"
            chmod --reference="$vid" "$thumb"
            chown --reference="$vid" "$thumb"
        fi
    done
done

echo "Thumbnails generated in '$THUMB_DIR' with preserved metadata."

