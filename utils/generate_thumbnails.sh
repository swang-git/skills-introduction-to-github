#!/bin/bash

# Check if directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <directory> [thumbnail_width]"
    exit 1
fi

# Set thumbnail width (default: 300px)
THUMBNAIL_WIDTH="${2:-300}"

# Navigate to the target directory
cd "$1" || exit

# Create thumbnails directory if it doesn't exist
THUMBNAILS_DIR="thumbnails"
mkdir -p "$THUMBNAILS_DIR"

# Process all .jpg and .jpeg files
for file in *.jpg *.jpeg; do
    # Generate thumbnail filename (e.g., "image.jpg" → "thumbnails/image_thumbnail.jpg")
    thumbnail_file="$THUMBNAILS_DIR/${file%.*}_thumbnail.${file##*.}"
    if [ -f "$file" ]; then
        # Skip if already a thumbnail
        # if [[ "$file" == *"thumbnail"* ]]; then
        if [ -f $thumbnail_file ]; then
            echo "thumbnail exist:[$thumbnail_file] process next ..."
            continue
        fi

        # Get the original modification time
        original_mtime=$(stat -f "%m" "$file")

        # Generate thumbnail filename (e.g., "image.jpg" → "thumbnails/image_thumbnail.jpg")
        # thumbnail_file="$THUMBNAILS_DIR/${file%.*}_thumbnail.${file##*.}"

        # Resize using sips (maintain aspect ratio)
        sips --resampleWidth "$THUMBNAIL_WIDTH" "$file" --out "$thumbnail_file" &>/dev/null

        if [ $? -eq 0 ]; then
            # Restore original modification time
            touch -m -t "$(date -r "$original_mtime" +"%Y%m%d%H%M.%S")" "$thumbnail_file"
            echo "✅ Generated thumbnail: $thumbnail_file (${THUMBNAIL_WIDTH}px)"
        else
            echo "❌ Failed to generate thumbnail: $file"
        fi
    fi
done

echo "✨ Thumbnail generation complete!"
